<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Service for publishing package resources.
 */
class ResourcePublisherService
{
    /**
     * Create a new ResourcePublisherService instance.
     *
     * @param Application $app
     * @param Filesystem $filesystem
     */
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $filesystem
    ) {}

    /**
     * Get all publishable resources with their tags.
     *
     * @return array<string, array<string, string>>
     */
    public function getPublishableResources(): array
    {
        return [
            'config' => [
                'source' => $this->app->basePath('vendor/roster/config/roster.php'),
                'destination' => $this->app->configPath('roster.php'),
                'tag' => 'roster-config'
            ],
            'migrations' => [
                'source' => $this->app->basePath('vendor/roster/database/migrations'),
                'destination' => $this->app->databasePath('migrations'),
                'tag' => 'roster-migrations'
            ],
            'views' => [
                'source' => $this->app->basePath('vendor/roster/resources/views'),
                'destination' => $this->app->resourcePath('views/vendor/roster'),
                'tag' => 'roster-views'
            ],
            'routes' => [
                'source' => $this->app->basePath('vendor/roster/routes/web.php'),
                'destination' => $this->app->basePath('routes/roster.php'),
                'tag' => 'roster-routes'
            ]
        ];
    }

    /**
     * Publish a specific resource.
     *
     * @param string $resourceType
     * @param bool $force
     * @param OutputInterface|null $output
     * @return bool
     */
    public function publishResource(
        string $resourceType,
        bool $force = false,
        ?OutputInterface $output = null
    ): bool {
        $resources = $this->getPublishableResources();

        if (!isset($resources[$resourceType])) {
            return false;
        }

        $config = $resources[$resourceType];

        if ($this->shouldPublishDirectory($config['source'])) {
            return $this->publishDirectory($config['source'], $config['destination'], $force, $output);
        }

        return $this->publishFile($config['source'], $config['destination'], $force, $output);
    }

    /**
     * Check if a source should be treated as a directory.
     *
     * @param string $source
     * @return bool
     */
    private function shouldPublishDirectory(string $source): bool
    {
        return is_dir($source) || str_contains($source, 'migrations') || str_contains($source, 'views');
    }

    /**
     * Publish a directory of resources.
     *
     * @param string $source
     * @param string $destination
     * @param bool $force
     * @param OutputInterface|null $output
     * @return bool
     */
    private function publishDirectory(
        string $source,
        string $destination,
        bool $force,
        ?OutputInterface $output
    ): bool {
        if (!$this->filesystem->exists($source)) {
            return false;
        }

        $files = $this->filesystem->allFiles($source);
        $publishedCount = 0;

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $targetPath = $destination . '/' . $relativePath;

            if ($this->shouldCopyFile($file->getPathname(), $targetPath, $force)) {
                $this->filesystem->ensureDirectoryExists(dirname($targetPath));
                $this->filesystem->copy($file->getPathname(), $targetPath);
                $publishedCount++;

                if ($output) {
                    $output->writeln("<info>Published:</info> {$relativePath}");
                }
            }
        }

        return $publishedCount > 0;
    }

    /**
     * Publish a single file.
     *
     * @param string $source
     * @param string $destination
     * @param bool $force
     * @param OutputInterface|null $output
     * @return bool
     */
    private function publishFile(
        string $source,
        string $destination,
        bool $force,
        ?OutputInterface $output
    ): bool {
        if (!$this->filesystem->exists($source)) {
            return false;
        }

        if ($this->shouldCopyFile($source, $destination, $force)) {
            $this->filesystem->ensureDirectoryExists(dirname($destination));
            $this->filesystem->copy($source, $destination);

            if ($output) {
                $output->writeln("<info>Published:</info> " . basename($destination));
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if a file should be copied.
     *
     * @param string $source
     * @param string $destination
     * @param bool $force
     * @return bool
     */
    private function shouldCopyFile(string $source, string $destination, bool $force): bool
    {
        if ($force) {
            return true;
        }

        return !$this->filesystem->exists($destination);
    }

    /**
     * Check if a resource has already been published.
     *
     * @param string $resourceType
     * @return bool
     */
    public function isPublished(string $resourceType): bool
    {
        $resources = $this->getPublishableResources();

        if (!isset($resources[$resourceType])) {
            return false;
        }

        $config = $resources[$resourceType];

        if ($this->shouldPublishDirectory($config['source'])) {
            return $this->filesystem->exists($config['destination']) &&
                $this->filesystem->isDirectory($config['destination']);
        }

        return $this->filesystem->exists($config['destination']);
    }
}
