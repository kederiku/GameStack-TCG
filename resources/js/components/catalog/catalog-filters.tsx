import { router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { FilterSelect } from '@/components/filter-select';
import { SegmentedPicker } from '@/components/segmented-picker';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { createFilterNavigation } from '@/lib/filter-navigation';
import { useLocale } from '@/hooks/use-locale';
import type { CatalogData, CatalogFilters as Filters } from '@/types/catalog';
import { useCatalogLabels } from './use-catalog-labels';

export function CatalogFilters({
    data,
    url,
}: {
    data: CatalogData;
    url: string;
}) {
    const { t, locale, languageName } = useLocale();
    const { types } = useCatalogLabels();
    const [query, setQuery] = useState(data.filters.q);
    const navigation = useMemo(
        () =>
            createFilterNavigation(data.filters, (filters, replace) => {
                let cancel = () => {};
                router.get(
                    url,
                    { ...filters, lang: locale },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        replace,
                        onCancelToken: (token) => {
                            cancel = () => token.cancel();
                        },
                    },
                );
                return () => cancel();
            }),
        [data.filters, url, locale],
    );
    useEffect(() => {
        setQuery(data.filters.q);
        const unsubscribe = router.on('before', navigation.cancel);
        window.addEventListener('popstate', navigation.cancel);
        return () => {
            unsubscribe();
            window.removeEventListener('popstate', navigation.cancel);
            navigation.cancel();
        };
    }, [navigation, data.filters.q]);
    function update(values: Partial<Filters>) {
        navigation.change(values);
    }
    const options = data.languages.map((language) => ({
        value: language.code,
        label: languageName(language.code, language.name),
    }));
    if (
        data.filters.language !== '*' &&
        !options.some((item) => item.value === data.filters.language)
    ) {
        options.push({
            value: data.filters.language,
            label: languageName(data.filters.language),
        });
    }
    return (
        <div className="space-y-6">
            {data.catalogs.length > 0 && (
                <SegmentedPicker
                    label={t('Catalogues', 'Catalogs')}
                    value={data.filters.catalog}
                    onValueChange={(catalog) =>
                        update({ catalog, language: '*', series: '' })
                    }
                    options={[
                        {
                            value: '*',
                            label: t('Tous', 'All'),
                            count: data.total,
                        },
                        ...data.catalogs.map((catalog) => ({
                            value: catalog.key,
                            label: catalog.name,
                            count: catalog.count,
                        })),
                    ]}
                />
            )}
            <form
                className="grid grid-cols-2 items-end gap-3 md:grid-cols-[minmax(0,1fr)_10rem_9rem_9rem]"
                role="search"
                onSubmit={(event) => {
                    event.preventDefault();
                    update({ q: query });
                }}
            >
                <div className="relative col-span-2 md:col-span-1">
                    <Label htmlFor="catalog-search" className="sr-only">
                        {t('Rechercher une extension', 'Search expansions')}
                    </Label>
                    <Search
                        className="pointer-events-none absolute top-3.5 left-3.5 size-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <Input
                        id="catalog-search"
                        type="search"
                        maxLength={150}
                        value={query}
                        className="h-11 bg-card pr-10 pl-10 text-sm"
                        placeholder={t(
                            'Rechercher une extension…',
                            'Search expansions…',
                        )}
                        onChange={(event) => {
                            const next = event.target.value;
                            setQuery(next);
                            navigation.change({ q: next }, true);
                        }}
                    />
                    {query && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="absolute top-0.5 right-1 size-10"
                            aria-label={t(
                                'Effacer la recherche',
                                'Clear search',
                            )}
                            onClick={() => {
                                setQuery('');
                                update({ q: '' });
                            }}
                        >
                            <X className="size-4" />
                        </Button>
                    )}
                </div>
                <FilterSelect
                    label={t('Langue des éditions', 'Edition language')}
                    value={data.filters.language}
                    onValueChange={(language) => update({ language })}
                    options={[
                        {
                            value: '*',
                            label: t('Toutes les langues', 'All languages'),
                        },
                        ...options,
                    ]}
                />
                <FilterSelect
                    label={t('Type', 'Type')}
                    value={data.filters.type}
                    onValueChange={(type) => update({ type })}
                    options={[
                        {
                            value: 'all',
                            label: t('Tous les types', 'All types'),
                        },
                        ...Object.entries(types).map(([value, label]) => ({
                            value,
                            label,
                        })),
                    ]}
                />
                <FilterSelect
                    label={t('Trier', 'Sort')}
                    value={data.filters.sort}
                    onValueChange={(sort) => update({ sort })}
                    options={[
                        {
                            value: 'recent',
                            label: t('Plus récentes', 'Newest first'),
                        },
                        {
                            value: 'oldest',
                            label: t('Plus anciennes', 'Oldest first'),
                        },
                        { value: 'name', label: t('Nom A–Z', 'Name A–Z') },
                    ]}
                />
            </form>
        </div>
    );
}
