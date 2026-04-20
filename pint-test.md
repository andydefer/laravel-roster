# Pint Formatting Test Report
*Generated: lun. 20 avril 2026 23:48:57 WAT*


  ⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯...⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯...⨯...⨯⨯⨯..⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.⨯.⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯...⨯⨯.

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ............................................................................................................................................... 178 files, 152 style issues  
  ⨯ config/roster.php                                                                                                                             phpdoc_no_package, phpdoc_separation  
  ⨯ database/migrations/2024_01_01_000001_create_roster_availabilities_table.php class_definition, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, no_unused_imports, no_ex…  
  ⨯ database/migrations/2026_04_16_000001_alter_validity_columns_to_datetime_on_roster_availabilities_table.php            class_definition, braces_position, single_blank_line_at_eof  
  ⨯ rector.php                                                                                                                                                            concat_space  
  ⨯ src/Casts/TimezoneAwareDateTimeCast.php                                                                                                              ordered_imports, phpdoc_align  
  ⨯ src/Commands/CacheRulesCommand.php                                                                                         concat_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Commands/DebugRulesCommand.php class_attributes_separation, function_declaration, increment_style, single_quote, concat_space, trailing_comma_in_multiline, not_operator_with…  
  ⨯ src/Commands/InstallRosterCommand.php                                                                                                                  blank_line_before_statement  
  ⨯ src/Commands/ListRulesCommand.php           single_quote, concat_space, trailing_comma_in_multiline, no_unused_imports, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/Config/RosterConfig.php                                                class_attributes_separation, no_superfluous_phpdoc_tags, no_trailing_whitespace_in_comment, phpdoc_trim  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                                                       phpdoc_separation, phpdoc_align  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                                           ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Repository/RepositoryInterface.php                                                                                                                      phpdoc_align  
  ⨯ src/Contracts/Repository/ScheduleRepositoryInterface.php                                                                 no_superfluous_phpdoc_tags, ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Services/ScheduleServiceInterface.php                                                                          no_superfluous_phpdoc_tags, phpdoc_trim, phpdoc_align  
  ⨯ src/Contracts/Services/ServiceInterface.php                                                                                  no_superfluous_phpdoc_tags, phpdoc_trim, phpdoc_align  
  ⨯ src/Contracts/Validation/RuleInterface.php                                                                                                         phpdoc_separation, phpdoc_align  
  ⨯ src/Contracts/Validation/ValidationContextInterface.php                                                                                                               phpdoc_align  
  ⨯ src/Contracts/Validation/ValidatorInterface.php                                                                                                                       phpdoc_align  
  ⨯ src/DTOs/AbstractDto.php                                                                          function_declaration, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/DTOs/AvailabilityDto.php                                                                                 not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  ⨯ src/DTOs/DataInterface.php                                                                                                                                            phpdoc_align  
  ⨯ src/DTOs/ImpedimentDto.php                                                                                                   braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/ScheduleDto.php                                                                                                     braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Domain/DTOs/CacheStats.php increment_style, concat_space, unary_operator_spaces, braces_position, phpdoc_separation, not_operator_with_successor_space, single_line_empty_bod…  
  ⨯ src/Domain/DTOs/ConflictResult.php                                                                                           braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Domain/Helpers/TimeSlotHelper.php                                                                             not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Domain/Helpers/TimeWindowHelper.php                                                                         phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Domain/Helpers/TimezoneHelper.php               concat_space, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
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
  ⨯ src/Models/Impediment.php                                                                                               blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                                                               phpdoc_align  
  ⨯ src/Observers/EnforceDomainMutationObserver.php                                                                 phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Repositories/AbstractRepository.php no_superfluous_phpdoc_tags, concat_space, phpdoc_separation, phpdoc_trim, not_operator_with_successor_space, blank_line_before_statement,…  
  ⨯ src/Repositories/AvailabilityRepository.php                                        trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Repositories/ImpedimentRepository.php                                                                                                                             phpdoc_align  
  ⨯ src/Repositories/ScheduleRepository.php                                                     new_with_parentheses, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/RosterServiceProvider.php                                                                                              concat_space, trailing_comma_in_multiline, phpdoc_align  
  ⨯ src/Services/AvailabilityService.php function_declaration, no_superfluous_phpdoc_tags, trailing_comma_in_multiline, phpdoc_separation, phpdoc_trim, not_operator_with_successor_s…  
  ⨯ src/Services/Core/AbstractService.php no_superfluous_phpdoc_tags, concat_space, trailing_comma_in_multiline, phpdoc_separation, phpdoc_trim, not_operator_with_successor_space, b…  
  ⨯ src/Services/ImpedimentService.php                    class_attributes_separation, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/ScheduleService.php no_superfluous_phpdoc_tags, concat_space, phpdoc_separation, phpdoc_trim, cast_spaces, not_operator_with_successor_space, blank_line_before_stat…  
  ⨯ src/Support/RosterMutationContext.php                                                   increment_style, phpdoc_no_package, phpdoc_trim, blank_line_before_statement, phpdoc_align  
  ⨯ src/Support/RosterServiceContext.php                                                                                    increment_style, blank_line_before_statement, phpdoc_align  
  ⨯ src/Traits/AttachableToSchedules.php                                                                                        no_superfluous_phpdoc_tags, concat_space, phpdoc_align  
  ⨯ src/Traits/BelongsToSchedulable.php                                                                                                                phpdoc_separation, phpdoc_align  
  ⨯ src/Traits/HasRoster.php                                                                                                           not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Cache/RuleCacheGenerator.php                                   function_declaration, concat_space, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Context/ValidationContext.php class_attributes_separation, function_declaration, concat_space, phpdoc_separation, not_operator_with_successor_space, ordered_impor…  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php function_declaration, single_quote, concat_space, not_operator_with_successor_space, no_extra_blank_lines, blank_line_bef…  
  ⨯ src/Validation/RuleScanner.php new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, blank…  
  ⨯ src/Validation/Rules/AbstractRule.php                                  concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                  single_quote, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityDaysCoherenceRule.php                                                not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityDaysInPeriodRule.php single_quote, no_superfluous_phpdoc_tags, unary_operator_spaces, phpdoc_trim, not_operator_with_successor_space, no_extra_b…  
  ⨯ src/Validation/Rules/AvailabilityOverlapRule.php                                                                     single_quote, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityOwnershipRule.php         single_quote, trailing_comma_in_multiline, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/AvailabilityTemporalCoherenceRule.php function_declaration, single_quote, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, p…  
  ⨯ src/Validation/Rules/AvailabilityTypeRule.php                                                                                      not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/DaysValidationRule.php                                                           not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/DurationRule.php                                                                  single_quote, concat_space, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/FutureDateRule.php class_attributes_separation, concat_space, not_operator_with_successor_space, no_extra_blank_lines, blank_line_before_statement, phpdoc_a…  
  ⨯ src/Validation/Rules/ImpedimentScheduleDaysCoherenceRule.php                       phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/RequiredFieldsRule.php                                                                          single_quote, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/SchedulableConsistencyRule.php               single_quote, not_operator_with_successor_space, no_extra_blank_lines, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/SchedulableValidationRule.php                                                    not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Validation/Rules/TemporalConflictRule.php                               single_quote, braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Rules/TimeRangeRule.php                                                                                             not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/TimeSlotDateTimeRule.php                                       single_quote, concat_space, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Rules/TimezoneValidationRule.php                                                        single_quote, concat_space, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/ValidationResult.php                                                             class_attributes_separation, braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Validator.php                                      function_declaration, concat_space, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/helpers.php         increment_style, no_superfluous_phpdoc_tags, concat_space, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ tests/Feature/Integration/CompleteRosterIntegrationTest.php function_declaration, increment_style, single_quote, concat_space, trailing_comma_in_multiline, not_operator_with_suc…  
  ⨯ tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php                                                                                        blank_line_before_statement  
  ⨯ tests/Integration/Database/AvailabilityIntegrationTest.php                                                                                   no_extra_blank_lines, ordered_imports  
  ⨯ tests/Integration/Database/ImpedimentIntegrationTest.php                                                                                                           ordered_imports  
  ⨯ tests/Integration/Database/ScheduleIntegrationTest.php                                                                                                             ordered_imports  
  ⨯ tests/Integration/Traits/BelongsToSchedulableTest.php                                                                                                              ordered_imports  
  ⨯ tests/Support/TestDoctor.php                                                                                                                                        ordered_traits  
  ⨯ tests/Support/TestSchedulable.php                                                                                                                                   ordered_traits  
  ⨯ tests/TestCase.php                                                                                                                                      concat_space, phpdoc_align  
  ⨯ tests/Unit/Commands/CacheRulesCommandTest.php                                                                    new_with_parentheses, concat_space, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Commands/CapturesOutput.php                                                                                                                                phpdoc_align  
  ⨯ tests/Unit/Commands/DebugRulesCommandTest.php                                                                   new_with_parentheses, trailing_comma_in_multiline, ordered_imports  
  ⨯ tests/Unit/Commands/InstallRosterCommandTest.php                                                                 function_declaration, no_multiline_whitespace_around_double_arrow  
  ⨯ tests/Unit/Commands/ListRulesCommandTest.php                                                      new_with_parentheses, single_quote, trailing_comma_in_multiline, ordered_imports  
  ⨯ tests/Unit/DTOs/ImpedimentDataTest.php                                                                                                                             ordered_imports  
  ⨯ tests/Unit/DTOs/ScheduleDataTest.php                                                                                                                               ordered_imports  
  ⨯ tests/Unit/Domain/Helpers/TimezoneHelperTest.php                                                                                                                   ordered_imports  
  ⨯ tests/Unit/Domain/ModelMutationForbiddenTest.php                                                                                                                   ordered_imports  
  ⨯ tests/Unit/Domain/MutationContextAllowsMutationTest.php                                                                                              ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Domain/RepositoryMutationTest.php                                                                                                                       ordered_imports  
  ⨯ tests/Unit/HelpersTest.php                                                                             new_with_parentheses, braces_position, single_line_empty_body, phpdoc_align  
  ⨯ tests/Unit/Http/Middleware/SetUserTimezoneTest.php new_with_parentheses, function_declaration, php_unit_method_casing, braces_position, blank_line_before_statement, ordered_impo…  
  ⨯ tests/Unit/Http/Resources/AvailabilityResource.php                                                                                         no_superfluous_phpdoc_tags, phpdoc_trim  
  ⨯ tests/Unit/Http/Resources/ImpedimentResource.php                                                                                           no_superfluous_phpdoc_tags, phpdoc_trim  
  ⨯ tests/Unit/Http/Resources/ScheduleResource.php                                                                                             no_superfluous_phpdoc_tags, phpdoc_trim  
  ⨯ tests/Unit/Models/AttachableToSchedulesTest.php                                                                                                                  no_unused_imports  
  ⨯ tests/Unit/Models/AvailabilityTest.php                                                                                                               ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ImpedimentTest.php                                                                                                                 ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                            class_attributes_separation, no_unused_imports, binary_operator_spaces, phpdoc_align  
  ⨯ tests/Unit/RosterServiceProviderTest.php                                                                                                                           ordered_imports  
  ⨯ tests/Unit/Services/AvailabilityServiceFindTest.php                                                                                                phpdoc_separation, phpdoc_align  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                         class_attributes_separation, single_quote, phpdoc_separation, phpdoc_align  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php                                                   increment_style, single_quote, concat_space, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Services/ScheduleService/AvailabilitySearchTest.php                                                                                                   no_unused_imports  
  ⨯ tests/Unit/Services/ScheduleService/ConflictDetectionTest.php                                                                                                    no_unused_imports  
  ⨯ tests/Unit/Services/ScheduleService/ScheduleLinksAdvancedTest.php                                                                                      trailing_comma_in_multiline  
  ⨯ tests/Unit/Services/ScheduleService/ScheduleLinksMixedTypesTest.php                                                                 trailing_comma_in_multiline, no_unused_imports  
  ⨯ tests/Unit/Services/ScheduleService/ScheduleLinksTest.php                                                                                                        no_unused_imports  
  ⨯ tests/Unit/Services/ScheduleServiceDurationValidationTest.php                                                                                        concat_space, ordered_imports  
  ⨯ tests/Unit/Traits/HasRosterTest.php                                                                                                                   cast_spaces, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AbstractRuleTest.php                                                                                           braces_position, single_line_empty_body  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php              new_with_parentheses, function_declaration, increment_style, braces_position, phpdoc_trim, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php      new_with_parentheses, function_declaration, increment_style, braces_position, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/AvailabilityOverlapRuleTest.php                                                            new_with_parentheses, function_declaration, braces_position  
  ⨯ tests/Unit/Validation/Rules/AvailabilityOwnershipRuleTest.php                           new_with_parentheses, function_declaration, braces_position, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                       class_attributes_separation, new_with_parentheses, function_declaration, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityTemporalCoherenceRuleTest.php                                                                                single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityTypeRuleTest.php                                                   new_with_parentheses, function_declaration, trailing_comma_in_multiline  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                                 no_empty_statement, new_with_parentheses, function_declaration, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/DaysValidationRuleTest.php                                                                    new_with_parentheses, function_declaration, concat_space  
  ⨯ tests/Unit/Validation/Rules/DurationRuleTest.php                                                     class_attributes_separation, new_with_parentheses, single_quote, concat_space  
  ⨯ tests/Unit/Validation/Rules/FutureDateRuleTest.php                 new_with_parentheses, function_declaration, no_superfluous_phpdoc_tags, concat_space, phpdoc_trim, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php            new_with_parentheses, function_declaration, single_quote, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/RequiredFieldsRuleTest.php                                                                                       new_with_parentheses, increment_style  
  ⨯ tests/Unit/Validation/Rules/SchedulableConsistencyRuleTest.php                                                                 new_with_parentheses, braces_position, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/SchedulableValidationRuleTest.php                                                                  new_with_parentheses, braces_position, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/TimeRangeRuleTest.php                                                                                            new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/TimeSlotDateTimeRuleTest.php                              new_with_parentheses, braces_position, single_line_empty_body, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Validation/Rules/TimezoneValidationRuleTest.php    new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Validation/ValidationContextTest.php new_with_parentheses, trailing_comma_in_multiline, braces_position, not_operator_with_successor_space, no_blank_lines_after_phpdo…  
  ⨯ tests/Unit/Validation/ValidatorTest.php                                       new_with_parentheses, function_declaration, braces_position, single_line_empty_body, ordered_imports  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  
  ⨯ tests/database/migrations/2024_01_01_000000_create_test_cars_table.php                                        class_definition, braces_position, not_operator_with_successor_space  
  ⨯ tests/database/migrations/2024_01_01_000000_create_test_doctors_table copy.php                                class_definition, braces_position, not_operator_with_successor_space  
  ⨯ tests/database/migrations/2024_01_01_000000_create_test_rooms_table.php                                       class_definition, braces_position, not_operator_with_successor_space  
  ⨯ tests/database/migrations/2024_01_01_000000_create_test_schedulables_table.php                                class_definition, braces_position, not_operator_with_successor_space  

