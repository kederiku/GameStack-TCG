<?php

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class GarageConfigurationTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/garage-config-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
        file_put_contents($this->directory.'/.env', "APP_URL=http://localhost:8123\nAWS_ACCESS_KEY_ID=external-key\nAWS_SECRET_ACCESS_KEY=external-secret\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_configuration_is_private_repeatable_and_preserves_existing_environment(): void
    {
        $environment = file_get_contents($this->directory.'/.env');
        $first = $this->configure();
        $this->assertSame(0, $first->getExitCode(), $first->getErrorOutput().$first->getOutput());
        $path = $this->directory.'/storage/app/private/garage/';
        $files = [];
        foreach (['state.json', 'garage.toml', 'webui.env', 'login.txt'] as $file) {
            $files[$file] = file_get_contents($path.$file);
            $this->assertSame(0600, fileperms($path.$file) & 0777);
        }
        $state = json_decode($files['state.json'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('http://localhost:8123', $state['origin']);
        $this->assertTrue(password_verify(trim(explode('Password: ', $files['login.txt'])[1]), $state['password_hash']));

        $again = $this->configure();
        $this->assertSame(0, $again->getExitCode(), $again->getErrorOutput());
        foreach ($files as $file => $contents) {
            $this->assertSame($contents, file_get_contents($path.$file));
        }
        $this->assertSame($environment, file_get_contents($this->directory.'/.env'));
        foreach (['rpc_secret', 'admin_token', 'password_hash'] as $secret) {
            $this->assertStringNotContainsString($state[$secret], $first->getOutput().$again->getOutput());
        }
    }

    public function test_password_rotation_preserves_storage_identity(): void
    {
        $this->assertSame(0, $this->configure()->getExitCode());
        $file = $this->directory.'/storage/app/private/garage/state.json';
        $before = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(0, $this->configure('--password')->getExitCode());
        $after = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame($before['rpc_secret'], $after['rpc_secret']);
        $this->assertSame($before['admin_token'], $after['admin_token']);
        $this->assertNotSame($before['password_hash'], $after['password_hash']);
    }

    private function configure(?string $option = null): Process
    {
        $arguments = [PHP_BINARY, base_path('docker/garage/configure.php'), $this->directory];
        if ($option !== null) {
            $arguments[] = $option;
        }
        $process = new Process($arguments, env: ['APP_URL' => false, 'AWS_DEFAULT_REGION' => false, 'GARAGE_BUCKET' => false, 'GARAGE_WEB_PORT' => false]);
        $process->run();

        return $process;
    }
}
