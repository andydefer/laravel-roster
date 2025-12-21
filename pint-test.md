# Pint Formatting Test Report
*Generated: dim. 21 déc. 2025 17:41:22 WAT*


  .⨯⨯⨯⨯⨯......⨯.⨯..⨯.⨯.⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.....⨯⨯⨯⨯⨯⨯⨯⨯.⨯....⨯.......⨯....⨯⨯⨯⨯..⨯.⨯⨯⨯⨯...⨯....

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ................................................................................................................................................. 91 files, 47 style issues  
  ⨯ src/Contracts/Services/AvailabilityValidatorInterface.php                                                                                              class_attributes_separation  
  ⨯ src/Contracts/Services/CachableInterface.php                                                                                                                          phpdoc_align  
  ⨯ src/Contracts/Services/ConfigurableInterface.php                                                                                                   phpdoc_separation, phpdoc_align  
  ⨯ src/Contracts/Services/FilterableInterface.php                                                                                                                        phpdoc_align  
  ⨯ src/Contracts/Services/ValidatableInterface.php                                                                                                                       phpdoc_align  
  ⨯ src/Exceptions/Messages/ErrorMessageFactory.php                                                                                                         concat_space, phpdoc_align  
  ⨯ src/Exceptions/OverlappingImpedimentException.php                                                                                                                     phpdoc_align  
  ⨯ src/Exceptions/OverlappingScheduleException.php                                                                                                                       phpdoc_align  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  ⨯ src/Models/Availability.php                                                                                 unary_operator_spaces, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Models/Impediment.php                                                                                                                            ordered_imports, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                                              ordered_imports, phpdoc_align  
  ⨯ src/Observers/SchedulableObserver.php                                                                                                                                 phpdoc_align  
  ⨯ src/Repositories/AbstractRepository.php                                                                                                                               phpdoc_align  
  ⨯ src/Repositories/AvailabilityRepository.php function_declaration, no_multiline_whitespace_around_double_arrow, trailing_comma_in_multiline, braces_position, phpdoc_separation, n…  
  ⨯ src/Repositories/ImpedimentRepository.php                                                                                                                             phpdoc_align  
  ⨯ src/Repositories/ScheduleRepository.php                                                                                                                               phpdoc_align  
  ⨯ src/RosterServiceProvider.php                                                                                                                   concat_space, no_extra_blank_lines  
  ⨯ src/Services/AvailabilityService.php                                                                                not_operator_with_successor_space, blank_line_before_statement  
  ⨯ src/Services/Core/AbstractAvailabilityDependentService.php                                                      phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/Core/AbstractEntityScopingService.php   concat_space, ordered_traits, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Services/Core/AbstractService.php                                                           braces_position, single_line_empty_body, blank_line_before_statement, phpdoc_align  
  ⨯ src/Services/Core/AvailabilityChecker.php                                                braces_position, phpdoc_separation, single_line_empty_body, ordered_imports, phpdoc_align  
  ⨯ src/Services/Core/AvailabilityMerger.php                                            function_declaration, braces_position, phpdoc_separation, single_line_empty_body, phpdoc_align  
  ⨯ src/Services/Core/AvailabilityValidator.php class_attributes_separation, unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, no_extra_blank_lines, blank…  
  ⨯ src/Services/Core/Components/Cachable.php                                                                   unary_operator_spaces, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/Core/Components/ConfigurationRules.php                                                         unary_operator_spaces, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/Core/Components/ExceptionHandler.php                                                                                                  phpdoc_separation, phpdoc_align  
  ⨯ src/Services/Core/Components/LifecycleHooks.php                                                                                                                       phpdoc_align  
  ⨯ src/Services/Core/ResourcePublisherService.php             increment_style, concat_space, braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/Services/Core/SlotFinderService.php function_declaration, increment_style, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line_empty_body,…  
  ⨯ src/Services/Core/ValidationService.php                                    concat_space, unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/ImpedimentService.php                                 class_attributes_separation, ordered_traits, no_unused_imports, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/ScheduleService.php                                                                                                   not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Traits/BelongsToSchedulable.php                                                                                                                                ordered_imports  
  ⨯ src/Traits/DateRangeOverlapTrait.php                                                     unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Traits/FilterableTrait.php                                             concat_space, unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ tests/Feature/Facades/AvailabilityFacadeTest.php                                                                                                  braces_position, ordered_imports  
  ⨯ tests/Feature/Facades/ImpedimentFacadeTest.php                                                    class_attributes_separation, braces_position, no_unused_imports, ordered_imports  
  ⨯ tests/Feature/Facades/ScheduleFacadeTest.php                                                                        class_attributes_separation, class_definition, braces_position  
  ⨯ tests/Feature/Services/AvailabilityServiceTest.php                                                                                                braces_position, ordered_imports  
  ⨯ tests/Feature/Services/ScheduleServiceTest.php                                                                                  class_definition, braces_position, ordered_imports  
  ⨯ tests/Integration/ModelIntegrationTest.php                                                                                      class_definition, braces_position, ordered_imports  
  ⨯ tests/Integration/RepositoryIntegrationTest.php                                                                                 class_definition, braces_position, ordered_imports  
  ⨯ tests/Integration/ServiceIntegrationTest.php                                                                             class_definition, php_unit_method_casing, braces_position  
  ⨯ tests/TestCase.php                                                                                                                concat_space, no_extra_blank_lines, phpdoc_align  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  

