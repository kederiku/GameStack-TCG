<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/files.php';

use Aws\S3\S3Client;
use GuzzleHttp\Client;

$directory = dirname(__DIR__, 2).'/storage/app/private/garage';

try {
    $lock = fopen($directory.'/setup.lock', 'c');
    if ($lock === false || ! flock($lock, LOCK_EX)) {
        throw new RuntimeException('Impossible de verrouiller l’initialisation Garage.');
    }
    $state = json_decode(file_get_contents($directory.'/state.json'), true, flags: JSON_THROW_ON_ERROR);
    $client = new Client(['base_uri' => 'http://garage:3903', 'timeout' => 5, 'connect_timeout' => 2, 'http_errors' => false]);
    $api = function (string $operation, ?array $body = null, array $query = []) use ($client, $state): array {
        $options = ['headers' => ['Authorization' => 'Bearer '.$state['admin_token']], 'query' => $query];
        if ($body !== null) {
            $options['json'] = $body;
        }
        $response = $client->request($body === null ? 'GET' : 'POST', '/v2/'.$operation, $options);
        if ($response->getStatusCode() !== 200) {
            // Responses may contain keys. Keep errors useful without printing secrets.
            throw new RuntimeException('Garage '.$operation.' : HTTP '.$response->getStatusCode());
        }

        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    };

    $status = null;
    for ($attempt = 0; $attempt < 20; $attempt++) {
        try {
            $status = $api('GetClusterStatus');
            break;
        } catch (Throwable) {
            usleep(500000);
        }
    }
    if ($status === null) {
        throw new RuntimeException('Garage n’est pas disponible ; vérifier le service et sa configuration.');
    }
    $layout = $api('GetClusterLayout');
    if ($layout['version'] === 0) {
        if (count($status['nodes']) !== 1 || $layout['roles'] !== []) {
            throw new RuntimeException('Initialisation réservée à un nouveau nœud local.');
        }
        $role = ['id' => $status['nodes'][0]['id'], 'zone' => 'local', 'capacity' => 10000000000, 'tags' => ['gamestackr-dev']];
        foreach ($layout['stagedRoleChanges'] as $staged) {
            if ($staged != $role) {
                throw new RuntimeException('Des changements de layout inconnus sont en attente ; aucune modification automatique.');
            }
        }
        $api('UpdateClusterLayout', ['roles' => [$role]]);
        $api('ApplyClusterLayout', ['version' => 1]);
    }

    $buckets = array_values(array_filter($api('ListBuckets'), fn (array $bucket): bool => in_array($state['bucket'], $bucket['globalAliases'], true)));
    $bucket = $buckets[0] ?? $api('CreateBucket', ['globalAlias' => $state['bucket']]);
    $keyName = 'gamestackr-local-images';
    $keys = array_values(array_filter($api('ListKeys'), fn (array $key): bool => $key['name'] === $keyName));
    if (count($keys) > 1) {
        throw new RuntimeException('Plusieurs clés locales existent ; aucune clé n’a été remplacée.');
    }
    $key = $keys === []
        ? $api('CreateKey', ['name' => $keyName, 'neverExpires' => true])
        : $api('GetKeyInfo', query: ['id' => $keys[0]['id'], 'showSecretKey' => 'true']);
    if (empty($key['secretAccessKey']) || $key['expired']) {
        throw new RuntimeException('La clé locale est expirée ou illisible ; aucune rotation automatique.');
    }
    if (is_file($directory.'/s3.json')) {
        $previous = json_decode(file_get_contents($directory.'/s3.json'), true, flags: JSON_THROW_ON_ERROR);
        if ($previous['key'] !== $key['accessKeyId'] || $previous['secret'] !== $key['secretAccessKey']) {
            throw new RuntimeException('La clé du stockage a changé ; bascule automatique refusée.');
        }
    }
    $permissions = ['bucketId' => $bucket['id'], 'accessKeyId' => $key['accessKeyId'], 'permissions' => ['read' => true, 'write' => true, 'owner' => true]];
    $api('AllowBucketKey', $permissions);
    try {
        $api('UpdateBucket', ['websiteAccess' => ['enabled' => true, 'indexDocument' => 'index.html']], ['id' => $bucket['id']]);
        $s3 = new S3Client([
            'version' => 'latest', 'region' => $state['region'], 'endpoint' => 'http://garage:3900',
            'use_path_style_endpoint' => true,
            'credentials' => ['key' => $key['accessKeyId'], 'secret' => $key['secretAccessKey']],
            'http' => ['timeout' => 10, 'connect_timeout' => 2], 'retries' => 2,
        ]);
        $s3->putBucketCors(['Bucket' => $state['bucket'], 'CORSConfiguration' => ['CORSRules' => [[
            'AllowedOrigins' => [$state['origin']], 'AllowedMethods' => ['GET', 'HEAD'],
            'AllowedHeaders' => ['*'], 'ExposeHeaders' => ['ETag', 'Content-Type', 'Content-Length'], 'MaxAgeSeconds' => 3600,
        ]]]]);
    } finally {
        $api('DenyBucketKey', array_replace($permissions, ['permissions' => ['read' => false, 'write' => false, 'owner' => true]]));
    }
    writeGarageFile($directory.'/s3.json', json_encode([
        'key' => $key['accessKeyId'], 'secret' => $key['secretAccessKey'],
        'region' => $state['region'], 'bucket' => $state['bucket'],
        'endpoint' => 'http://garage:3900', 'url' => $state['url'],
    ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n");
    echo "Garage prêt : bucket {$state['bucket']}, accès application limités à la lecture/écriture.\n";
} catch (Throwable $exception) {
    // AWS/Guzzle exceptions can include signed requests. Avoid logging credentials.
    fwrite(STDERR, $exception::class === RuntimeException::class
        ? $exception->getMessage()."\n"
        : 'Échec de l’initialisation Garage ('.get_class($exception)."). Consulter l’état des services.\n");
    exit(1);
}
