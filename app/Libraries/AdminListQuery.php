<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Model;
use CodeIgniter\Pager\Pager;

/**
 * Tri et pagination sécurisés pour les tableaux du back-office (?sort= & ?dir=).
 */
final class AdminListQuery
{
    /** @var array<string, string> clé URL => colonne SQL */
    private array $allowedSorts;

    private string $sortKey;

    private string $sortDir;

    private ?string $secondaryColumn = null;

    private string $secondaryDir = 'ASC';

    /**
     * @param array<string, string> $allowedSorts
     * @param list<string>          $pagerQueryKeys paramètres GET conservés dans les liens de page
     */
    public function __construct(
        private Model $model,
        array $allowedSorts,
        private string $defaultSort,
        private string $defaultDir = 'desc',
        private int $perPage = 20,
        private array $pagerQueryKeys = [],
        private string $pagerGroup = 'default',
    ) {
        if ($allowedSorts === []) {
            throw new \InvalidArgumentException('AdminListQuery requires at least one sort column.');
        }

        $this->allowedSorts = $allowedSorts;
        $this->resolveSortFromRequest();
    }

    public function setSecondarySort(string $column, string $direction = 'ASC'): self
    {
        $this->secondaryColumn = $column;
        $dir                 = strtoupper($direction);
        $this->secondaryDir  = $dir === 'DESC' ? 'DESC' : 'ASC';

        return $this;
    }

    public function sortKey(): string
    {
        return $this->sortKey;
    }

    public function sortDir(): string
    {
        return strtolower($this->sortDir);
    }

    public function pager(): Pager
    {
        return $this->model->pager;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function paginate(): array
    {
        $preserve = array_values(array_unique(array_merge($this->pagerQueryKeys, ['sort', 'dir'])));
        service('pager')->only($preserve);

        $column = $this->allowedSorts[$this->sortKey];
        $builder = $this->model->orderBy($column, $this->sortDir);

        if (
            $this->secondaryColumn !== null
            && $this->secondaryColumn !== ''
            && $this->secondaryColumn !== $column
        ) {
            $builder = $builder->orderBy($this->secondaryColumn, $this->secondaryDir);
        }

        return $builder->paginate($this->perPage, $this->pagerGroup);
    }

    private function resolveSortFromRequest(): void
    {
        $req  = service('request');
        $sort = $req->getGet('sort');
        $dir  = $req->getGet('dir');

        if (is_string($sort) && isset($this->allowedSorts[$sort])) {
            $this->sortKey = $sort;
            $d             = is_string($dir) ? strtolower($dir) : '';
            $this->sortDir = in_array($d, ['asc', 'desc'], true) ? strtoupper($d) : 'ASC';

            return;
        }

        $this->sortKey = isset($this->allowedSorts[$this->defaultSort])
            ? $this->defaultSort
            : (string) array_key_first($this->allowedSorts);

        $default = strtoupper($this->defaultDir);
        $this->sortDir = $default === 'ASC' ? 'ASC' : 'DESC';
    }
}
