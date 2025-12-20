<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Service for publishing package resources like config files, migrations, views, and routes.
 */
class ResourcePublisherService
{
    /**
     * Create a new ResourcePublisherService instance.
     *
     * @param Application $application Laravel application instance
     * @param Filesystem $filesystem Filesystem instance for file operations
     */
    public function __construct(
        private readonly Application $application,
        private readonly Filesystem $filesystem
    ) {}

    /**
     * Get all publishable resources with their source, destination, and tags.
     *
     * @return array<string, array<string, string>> Resource types mapped to their configurations
     */
    public function getPublishableResources(): array
    {
        return [
            'config' => [
                'source' => $this->application->basePath('vendor/roster/config/roster.php'),
                'destination' => $this->application->configPath('roster.php'),
                'tag' => 'roster-config',
            ],
            'migrations' => [
                'source' => $this->application->basePath('vendor/roster/database/migrations'),
                'destination' => $this->application->databasePath('migrations'),
                'tag' => 'roster-migrations',
            ],
            'views' => [
                'source' => $this->application->basePath('vendor/roster/resources/views'),
                'destination' => $this->application->resourcePath('views/vendor/roster'),
                'tag' => 'roster-views',
            ],
            'routes' => [
                'source' => $this->application->basePath('vendor/roster/routes/web.php'),
                'destination' => $this->application->basePath('routes/roster.php'),
                'tag' => 'roster-routes',
            ],
        ];
    }

    /**
     * Publish a specific resource type.
     *
     * @param string $resourceType Type of resource to publish (config, migrations, views, routes)
     * @param bool $force Whether to force overwrite existing files
     * @param OutputInterface|null $output Console output interface for logging
     * @return bool True if any files were published, false otherwise
     */
    public function publishResource(
        string $resourceType,
        bool $force = false,
        ?OutputInterface $output = null
    ): bool {
        $resources = $this->getPublishableResources();

        if (!array_key_exists($resourceType, $resources)) {
            return false;
        }

        $config = $resources[$resourceType];

        if ($this->shouldTreatAsDirectory($config['source'])) {
            return $this->publishDirectory(
                source: $config['source'],
                destination: $config['destination'],
                force: $force,
                output: $output
            );
        }

        return $this->publishSingleFile(
            source: $config['source'],
            destination: $config['destination'],
            force: $force,
            output: $output
        );
    }

    /**
     * Check if a resource type has already been published.
     *
     * @param string $resourceType Type of resource to check
     * @return bool True if the resource exists at the destination
     */
    public function isPublished(string $resourceType): bool
    {
        $resources = $this->getPublishableResources();

        if (!array_key_exists($resourceType, $resources)) {
            return false;
        }

        $config = $resources[$resourceType];

        if ($this->shouldTreatAsDirectory($config['source'])) {
            return $this->filesystem->exists($config['destination']) &&
                $this->filesystem->isDirectory($config['destination']);
        }

        return $this->filesystem->exists($config['destination']);
    }

    /**
     * Determine if a source path should be treated as a directory.
     *
     * @param string $source Source path to check
     * @return bool True if the source should be published as a directory
     */
    private function shouldTreatAsDirectory(string $source): bool
    {
        return is_dir($source) || str_contains($source, 'migrations') || str_contains($source, 'views');
    }

    /**
     * Publish all files from a source directory to a destination directory.
     *
     * @param string $source Source directory path
     * @param string $destination Destination directory path
     * @param bool $force Whether to force overwrite existing files
     * @param OutputInterface|null $output Console output interface for logging
     * @return bool True if any files were published, false otherwise
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

            if ($this->shouldCopyFile(targetPath: $targetPath, force: $force)) {
                $this->filesystem->ensureDirectoryExists(dirname($targetPath));
                $this->filesystem->copy($file->getPathname(), $targetPath);
                ++$publishedCount;

                if ($output instanceof OutputInterface) {
                    $output->writeln('<info>Published:</info> ' . $relativePath);
                }
            }
        }

        return $publishedCount > 0;
    }

    /**
     * Publish a single file from source to destination.
     *
     * @param string $source Source file path
     * @param string $destination Destination file path
     * @param bool $force Whether to force overwrite existing file
     * @param OutputInterface|null $output Console output interface for logging
     * @return bool True if the file was published, false otherwise
     */
    private function publishSingleFile(
        string $source,
        string $destination,
        bool $force,
        ?OutputInterface $output
    ): bool {
        if (!$this->filesystem->exists($source)) {
            return false;
        }

        if ($this->shouldCopyFile(targetPath: $destination, force: $force)) {
            $this->filesystem->ensureDirectoryExists(dirname($destination));
            $this->filesystem->copy($source, $destination);

            if ($output instanceof OutputInterface) {
                $output->writeln('<info>Published:</info> ' . basename($destination));
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if a file should be copied based on existence and force flag.
     *
     * @param string $targetPath Destination path to check
     * @param bool $force Whether to force overwrite
     * @return bool True if the file should be copied
     */
    private function shouldCopyFile(string $targetPath, bool $force): bool
    {
        if ($force) {
            return true;
        }

        return !$this->filesystem->exists($targetPath);
    }
}
