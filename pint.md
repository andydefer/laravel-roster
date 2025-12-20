# Pint Code Formatter Report
*Generated: sam. 20 déc. 2025 09:41:43 WAT*


  ......................⨯....⨯...........................⨯...............⨯..........

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   .................................................................................................................................................. 82 files, 4 style issues  
  ⨯ src/RosterServiceProvider.php                                                                                                                                         concat_space  
  @@ -60,7 +60,7 @@
        */
       public function register(): void
       {
  -        $this->mergeConfigFrom(__DIR__ . '/../config/roster.php', 'roster');
  +        $this->mergeConfigFrom(__DIR__.'/../config/roster.php', 'roster');
   
           $this->registerCoreServices();
           $this->registerRepositories();
  @@ -157,22 +157,22 @@
       {
           // Configuration
           $this->publishes([
  -            __DIR__ . '/../config/roster.php' => config_path('roster.php'),
  +            __DIR__.'/../config/roster.php' => config_path('roster.php'),
           ], 'roster-config');
   
           // Migrations - préfixées avec roster_
           $this->publishes([
  -            __DIR__ . '/../database/migrations/' => database_path('migrations'),
  +            __DIR__.'/../database/migrations/' => database_path('migrations'),
           ], 'roster-migrations');
   
           // Views
           $this->publishes([
  -            __DIR__ . '/../resources/views' => resource_path('views/vendor/roster'),
  +            __DIR__.'/../resources/views' => resource_path('views/vendor/roster'),
           ], 'roster-views');
   
           // Routes
           $this->publishes([
  -            __DIR__ . '/../routes/web.php' => base_path('routes/roster.php'),
  +            __DIR__.'/../routes/web.php' => base_path('routes/roster.php'),
           ], 'roster-routes');
       }
   
  
  ⨯ src/Services/Core/ResourcePublisherService.php    increment_style, concat_space, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line_empty_body  
  @@ -106,10 +106,10 @@
               if ($this->shouldCopyFile($targetPath, $force)) {
                   $this->filesystem->ensureDirectoryExists(dirname($targetPath));
                   $this->filesystem->copy($file->getPathname(), $targetPath);
  -                ++$publishedCount;
  +                $publishedCount++;
   
                   if ($output instanceof OutputInterface) {
  -                    $output->writeln('Published: ' . $relativePath);
  +                    $output->writeln('Published: '.$relativePath);
                   }
               }
           }
  
  ⨯ src/Services/Core/SlotFinderService.php                         increment_style, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line_empty_body  
  @@ -54,7 +54,7 @@
               $type
           );
   
  -        for ($dayOffset = 0; $dayOffset < 30; ++$dayOffset) {
  +        for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
               $currentDate = $startDate->copy()->addDays($dayOffset)->startOfDay();
   
               /** @var Collection<int, Availability> $dailyAvailabilities */
  
  ⨯ tests/Unit/Commands/InstallRosterCommandTest.php                                                                                class_definition, braces_position, ordered_imports  
  @@ -4,14 +4,14 @@
   
   namespace Tests\Unit\Commands;
   
  -use ReflectionMethod;
  -use Symfony\Component\Console\Input\InputDefinition;
  -use Symfony\Component\Console\Input\InputOption;
  -use ReflectionClass;
   use Illuminate\Console\OutputStyle;
   use Illuminate\Foundation\Testing\RefreshDatabase;
  +use ReflectionClass;
  +use ReflectionMethod;
   use Roster\Commands\InstallRosterCommand;
   use Symfony\Component\Console\Input\ArrayInput;
  +use Symfony\Component\Console\Input\InputDefinition;
  +use Symfony\Component\Console\Input\InputOption;
   use Symfony\Component\Console\Output\Output;
   use Tests\TestCase;
   
  

