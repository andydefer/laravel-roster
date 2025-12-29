# Pint Formatting Test Report
*Generated: lun. 29 déc. 2025 01:54:20 WAT*


  ⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯....⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯...⨯...⨯⨯.⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯.

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ............................................................................................................................................... 146 files, 127 style issues  
  ⨯ config/roster.php                                                                                                                                           binary_operator_spaces  
  ⨯ database/migrations/2024_01_01_000001_create_roster_availabilities_table.php class_definition, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, no_unused_imports, no_ex…  
  ⨯ rector.php                                                                                                                                                            concat_space  
  ⨯ src/Casts/TimezoneAwareDateTimeCast.php                                                                                no_superfluous_phpdoc_tags, no_unused_imports, phpdoc_align  
  ⨯ src/Commands/CacheRulesCommand.php                                                                                         concat_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Commands/DebugRulesCommand.php function_declaration, single_quote, concat_space, trailing_comma_in_multiline, no_unused_imports, not_operator_with_successor_space, blank_lin…  
  ⨯ src/Commands/InstallRosterCommand.php                                                                                                                  blank_line_before_statement  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                                                                          phpdoc_align  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                                           ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Repository/RepositoryInterface.php                                                                                                                      phpdoc_align  
  ⨯ src/Contracts/Repository/ScheduleRepositoryInterface.php                                                                                             ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Services/ServiceInterface.php                                                                                                                           phpdoc_align  
  ⨯ src/Contracts/Validation/RuleInterface.php                                                                                                         phpdoc_separation, phpdoc_align  
  ⨯ src/Contracts/Validation/ValidationContextInterface.php                                                                                                               phpdoc_align  
  ⨯ src/Contracts/Validation/ValidatorInterface.php                                                                                                                       phpdoc_align  
  ⨯ src/DTOs/AbstractData.php                                                                         function_declaration, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/DTOs/AvailabilityData.php                                                                                                      not_operator_with_successor_space, phpdoc_align  
  ⨯ src/DTOs/DataInterface.php                                                                                                                                            phpdoc_align  
  ⨯ src/DTOs/ImpedimentData.php                                                                                                  braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/ScheduleData.php                                                                                                    braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Domain/DTOs/CacheStats.php increment_style, concat_space, unary_operator_spaces, braces_position, phpdoc_separation, not_operator_with_successor_space, single_line_empty_bod…  
  ⨯ src/Domain/DTOs/ConflictResult.php                                                                                           braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Domain/Helpers/TimeSlotHelper.php                                                                             not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Domain/Helpers/TimeWindowHelper.php                                                                         phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Domain/Helpers/TimezoneHelper.php class_attributes_separation, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_ali…  
  ⨯ src/Domain/Services/CacheRulesService.php single_quote, concat_space, unary_operator_spaces, braces_position, phpdoc_separation, not_operator_with_successor_space, single_line_e…  
  ⨯ src/Domain/Services/RosterInstallerService.php                                 unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Domain/Services/TemporalConflictService.php class_attributes_separation, function_declaration, no_multiline_whitespace_around_double_arrow, trailing_comma_in_multiline, brac…  
  ⨯ src/Enums/EntityType.php                                                                                                             concat_space, phpdoc_separation, phpdoc_align  
  ⨯ src/Exceptions/DirectServiceUsageException.php                                                                                                                        phpdoc_align  
  ⨯ src/Exceptions/ForbiddenModelMutationException.php                                                                                                                    phpdoc_align  
  ⨯ src/Exceptions/InvalidOwnerException.php                                                                                                                              phpdoc_align  
  ⨯ src/Exceptions/InvalidServiceContextException.php                                            no_multiline_whitespace_around_double_arrow, single_quote, concat_space, phpdoc_align  
  ⨯ src/Exceptions/MissingOwnerException.php                                                                                                                              phpdoc_align  
  ⨯ src/Exceptions/NotFoundException.php                                                                                        cast_spaces, blank_line_before_statement, phpdoc_align  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  ⨯ src/Http/Middleware/SetUserTimezone.php                                                               not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Models/Availability.php                                                                           class_attributes_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Models/Impediment.php                                                                                                                blank_line_before_statement, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                                                               phpdoc_align  
  ⨯ src/Observers/EnforceDomainMutationObserver.php                                                                 phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Repositories/AbstractRepository.php no_multiline_whitespace_around_double_arrow, concat_space, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statem…  
  ⨯ src/Repositories/AvailabilityRepository.php                                        trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Repositories/ImpedimentRepository.php                                                                                                                             phpdoc_align  
  ⨯ src/Repositories/ScheduleRepository.php                                                                                                                               phpdoc_align  
  ⨯ src/RosterServiceProvider.php                                                                                                                           concat_space, phpdoc_align  
  ⨯ src/Services/AvailabilityService.php                                                                                  trailing_comma_in_multiline, phpdoc_separation, phpdoc_align  
  ⨯ src/Services/Core/AbstractService.php concat_space, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, ordered_impor…  
  ⨯ src/Services/ImpedimentService.php                    class_attributes_separation, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/ScheduleService.php                                                         class_attributes_separation, cast_spaces, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Support/RosterMutationContext.php                                                   increment_style, phpdoc_no_package, phpdoc_trim, blank_line_before_statement, phpdoc_align  
  ⨯ src/Support/RosterServiceContext.php                                                                                    increment_style, blank_line_before_statement, phpdoc_align  
  ⨯ src/Traits/BelongsToSchedulable.php                                                                                                                phpdoc_separation, phpdoc_align  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Cache/RuleCacheGenerator.php      function_declaration, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Context/ValidationContext.php               function_declaration, concat_space, phpdoc_separation, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php                                                                                                 concat_space, phpdoc_align  
  ⨯ src/Validation/RuleScanner.php new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, blank…  
  ⨯ src/Validation/Rules/AbstractRule.php                                                                 not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                                not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityDaysCoherenceRule.php                   class_attributes_separation, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityOverlapRule.php                                                                                   not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityOwnershipRule.php                       trailing_comma_in_multiline, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityTemporalCoherenceRule.php                    function_declaration, single_quote, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityTypeRule.php                                                                                                    not_operator_with_successor_space  
  ⨯ src/Validation/Rules/DaysValidationRule.php                                                           not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/DurationRule.php                                                                  single_quote, concat_space, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/FutureDateRule.php                                                                                            not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/ImpedimentScheduleDaysCoherenceRule.php                       phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/RequiredFieldsRule.php                                                                  not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  ⨯ src/Validation/Rules/SchedulableConsistencyRule.php                                                   not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/SchedulableValidationRule.php                                                    not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/ScheduleOverlapRule.php                                              braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Rules/TimeRangeRule.php                                                      not_operator_with_successor_space, blank_line_before_statement, binary_operator_spaces  
  ⨯ src/Validation/Rules/TimeSlotDateTimeRule.php                                       single_quote, concat_space, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/TimezoneValidationRule.php                                                        no_superfluous_phpdoc_tags, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/ValidationResult.php                                                                                                                                   phpdoc_align  
  ⨯ src/Validation/Validator.php                                                function_declaration, concat_space, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/helpers.php                          no_superfluous_phpdoc_tags, concat_space, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ tests/Feature/Integration/CompleteRosterIntegrationTest.php function_declaration, increment_style, single_quote, no_superfluous_phpdoc_tags, concat_space, trailing_comma_in_mult…  
  ⨯ tests/Integration/Database/AvailabilityIntegrationTest.php                                                                                      no_unused_imports, ordered_imports  
  ⨯ tests/Integration/Database/ImpedimentIntegrationTest.php                                                                                                           ordered_imports  
  ⨯ tests/Integration/Database/ScheduleIntegrationTest.php                                                                                                             ordered_imports  
  ⨯ tests/Integration/Traits/BelongsToSchedulableTest.php                                                                                                              ordered_imports  
  ⨯ tests/TestCase.php                                                                                             no_superfluous_phpdoc_tags, concat_space, phpdoc_trim, phpdoc_align  
  ⨯ tests/Unit/Commands/CacheRulesCommandTest.php                                                                    new_with_parentheses, concat_space, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Commands/CapturesOutput.php                                                                                                                                phpdoc_align  
  ⨯ tests/Unit/Commands/DebugRulesCommandTest.php                          new_with_parentheses, no_superfluous_phpdoc_tags, trailing_comma_in_multiline, phpdoc_trim, ordered_imports  
  ⨯ tests/Unit/Commands/InstallRosterCommandTest.php                                                                 function_declaration, no_multiline_whitespace_around_double_arrow  
  ⨯ tests/Unit/DTOs/AvailabilityDataTest.php                                                                                         no_unused_imports, phpdoc_single_line_var_spacing  
  ⨯ tests/Unit/DTOs/ImpedimentDataTest.php                                                                                             phpdoc_single_line_var_spacing, ordered_imports  
  ⨯ tests/Unit/DTOs/ScheduleDataTest.php                                                                                                                phpdoc_single_line_var_spacing  
  ⨯ tests/Unit/Domain/Helpers/TimezoneHelperTest.php                                                                                                phpdoc_separation, ordered_imports  
  ⨯ tests/Unit/Domain/ModelMutationForbiddenTest.php                                                                                                                   ordered_imports  
  ⨯ tests/Unit/Domain/MutationContextAllowsMutationTest.php                                                                  no_superfluous_phpdoc_tags, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Domain/RepositoryMutationTest.php                                                                                                                       ordered_imports  
  ⨯ tests/Unit/HelpersTest.php                                    new_with_parentheses, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, single_line_empty_body, phpdoc_align  
  ⨯ tests/Unit/Http/Middleware/SetUserTimezoneTest.php new_with_parentheses, function_declaration, php_unit_method_casing, braces_position, blank_line_before_statement, ordered_impo…  
  ⨯ tests/Unit/Models/AvailabilityTest.php                                                                                                               ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ImpedimentTest.php                                                                                                                 ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                                               class_attributes_separation, binary_operator_spaces, phpdoc_align  
  ⨯ tests/Unit/RosterServiceProviderTest.php                                                                                                                           ordered_imports  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                                                                                       single_quote  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php                                    increment_style, single_quote, concat_space, cast_spaces, ordered_imports, binary_operator_spaces  
  ⨯ tests/Unit/Services/ScheduleServiceTest.php                                                                                                                        ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php class_attributes_separation, new_with_parentheses, function_declaration, increment_style, no_superfluous_phpdoc_tag…  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php class_attributes_separation, new_with_parentheses, function_declaration, increment_style, braces_position, orde…  
  ⨯ tests/Unit/Validation/Rules/AvailabilityOverlapRuleTest.php                               class_attributes_separation, new_with_parentheses, function_declaration, braces_position  
  ⨯ tests/Unit/Validation/Rules/AvailabilityOwnershipRuleTest.php new_with_parentheses, function_declaration, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, ordered_impor…  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                                             class_attributes_separation, new_with_parentheses, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityTemporalCoherenceRuleTest.php                                                                                                 single_quote  
  ⨯ tests/Unit/Validation/Rules/AvailabilityTypeRuleTest.php                                                   new_with_parentheses, function_declaration, trailing_comma_in_multiline  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                                              class_attributes_separation, new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/DaysValidationRuleTest.php                                                                    new_with_parentheses, function_declaration, concat_space  
  ⨯ tests/Unit/Validation/Rules/DurationRuleTest.php                                                      new_with_parentheses, single_quote, no_superfluous_phpdoc_tags, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/FutureDateRuleTest.php                                                                                      new_with_parentheses, function_declaration  
  ⨯ tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php     class_attributes_separation, new_with_parentheses, function_declaration, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/RequiredFieldsRuleTest.php                                                                                     new_with_parentheses, no_unused_imports  
  ⨯ tests/Unit/Validation/Rules/SchedulableConsistencyRuleTest.php                                                                 new_with_parentheses, braces_position, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/SchedulableValidationRuleTest.php                         new_with_parentheses, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/TimeRangeRuleTest.php                                                               class_attributes_separation, new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/TimeSlotDateTimeRuleTest.php class_attributes_separation, new_with_parentheses, no_superfluous_phpdoc_tags, phpdoc_trim, ordered_imports, phpdoc_alig…  
  ⨯ tests/Unit/Validation/Rules/TimezoneValidationRuleTest.php                         class_attributes_separation, new_with_parentheses, trailing_comma_in_multiline, ordered_imports  
  ⨯ tests/Unit/Validation/ValidationContextTest.php class_attributes_separation, new_with_parentheses, trailing_comma_in_multiline, braces_position, not_operator_with_successor_spac…  
  ⨯ tests/Unit/Validation/ValidatorTest.php class_attributes_separation, new_with_parentheses, function_declaration, trailing_comma_in_multiline, braces_position, no_unused_imports,…  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  
  ⨯ tests/database/migrations/2024_01_01_000000_create_test_schedulables_table.php class_definition, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, not_operator_with_succ…  

