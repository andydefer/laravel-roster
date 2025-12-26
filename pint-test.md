# Pint Formatting Test Report
*Generated: ven. 26 déc. 2025 23:57:44 WAT*


  ..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯⨯⨯⨯⨯⨯⨯...⨯⨯⨯⨯⨯.⨯⨯..⨯⨯⨯⨯⨯⨯..⨯.....⨯⨯⨯⨯⨯.⨯⨯⨯⨯....⨯....⨯.......⨯......⨯....⨯⨯⨯⨯⨯.........⨯....⨯....⨯⨯⨯⨯⨯..⨯....⨯⨯⨯.....⨯..⨯.

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ................................................................................................................................................ 151 files, 77 style issues  
  ⨯ database/migrations/2024_01_01_000001_create_roster_availabilities_table.php         class_definition, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, no_unused_imports  
  ⨯ src/Commands/CacheRulesCommand.php                                                                                                                      concat_space, phpdoc_align  
  ⨯ src/Commands/InstallRosterCommand.php                                                                                     class_attributes_separation, blank_line_before_statement  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                                                                          phpdoc_align  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                                           ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Repository/RepositoryInterface.php                                                                                                                      phpdoc_align  
  ⨯ src/Contracts/Repository/ScheduleRepositoryInterface.php                                                                                             ordered_imports, phpdoc_align  
  ⨯ src/Contracts/RosterDataInterface.php                                                        class_definition, braces_position, single_line_empty_body, blank_line_after_namespace  
  ⨯ src/DTOs/AvailabilityData.php class_attributes_separation, function_declaration, braces_position, cast_spaces, not_operator_with_successor_space, single_line_empty_body, no_extr…  
  ⨯ src/DTOs/ImpedimentData.php                                                                            function_declaration, braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/ScheduleData.php                                                                   function_declaration, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Domain/DTOs/CacheStats.php                                     concat_space, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line_empty_body  
  ⨯ src/Domain/Helpers/TimeSlotHelper.php                                                          phpdoc_separation, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Domain/Services/RosterInstallerService.php                    unary_operator_spaces, not_operator_with_successor_space, single_line_after_imports, blank_line_before_statement  
  ⨯ src/Domain/Services/TemporalConflictService.php function_declaration, no_multiline_whitespace_around_double_arrow, trailing_comma_in_multiline, braces_position, phpdoc_separatio…  
  ⨯ src/Enums/EntityType.php                                                                                                                                              concat_space  
  ⨯ src/Exceptions/InvalidServiceContextException.php                                                                                                       single_quote, concat_space  
  ⨯ src/Exceptions/MergeConflictException.php                                                                                                               concat_space, phpdoc_align  
  ⨯ src/Exceptions/MissingOwnerException.php                                                                                                               blank_line_before_statement  
  ⨯ src/Exceptions/NotFoundException.php                                                                                        cast_spaces, blank_line_before_statement, phpdoc_align  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  ⨯ src/Models/Availability.php                                                                           not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Models/Impediment.php                                                                                                                       function_declaration, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                                              ordered_imports, phpdoc_align  
  ⨯ src/Observers/EnforceDomainMutationObserver.php                                                                                                  not_operator_with_successor_space  
  ⨯ src/Repositories/AbstractRepository.php no_multiline_whitespace_around_double_arrow, concat_space, phpdoc_separation, not_operator_with_successor_space, blank_line_before_statem…  
  ⨯ src/Repositories/AvailabilityRepository.php                                                                         trailing_comma_in_multiline, not_operator_with_successor_space  
  ⨯ src/RosterServiceProvider.php                            class_attributes_separation, concat_space, braces_position, single_line_empty_body, no_extra_blank_lines, ordered_imports  
  ⨯ src/Services/AvailabilityService.php                                                               trailing_comma_in_multiline, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Services/Core/AbstractService.php concat_space, trailing_comma_in_multiline, braces_position, phpdoc_separation, not_operator_with_successor_space, single_line_empty_body, b…  
  ⨯ src/Services/ImpedimentService.php                                    class_attributes_separation, trailing_comma_in_multiline, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Services/ScheduleService.php                                                      trailing_comma_in_multiline, cast_spaces, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Support/RosterMutationContext.php                                                                                                 increment_style, blank_line_before_statement  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Cache/RuleCacheGenerator.php function_declaration, blank_line_after_opening_tag, concat_space, not_operator_with_successor_space, blank_line_before_statement, ord…  
  ⨯ src/Validation/Context/ValidationContext.php                                      function_declaration, not_operator_with_successor_space, ordered_imports, binary_operator_spaces  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php                                                                                              concat_space, ordered_imports  
  ⨯ src/Validation/RuleScanner.php class_attributes_separation, new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, not_operator_with_successor_sp…  
  ⨯ src/Validation/Rules/AbstractRule.php                                                                                                                  blank_line_before_statement  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                  single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityDaysCoherenceRule.php                                             not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityOverlapRule.php                                                                                not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityOwnershipRule.php                           not_operator_with_successor_space, no_extra_blank_lines, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityTemporalCoherenceRule.php function_declaration, single_quote, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, b…  
  ⨯ src/Validation/Rules/AvailabilityTypeRule.php                                                             not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  ⨯ src/Validation/Rules/DaysValidationRule.php                                                        not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/DurationRule.php                                                 single_quote, concat_space, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/FutureDateRule.php                                                                                         not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/ImpedimentScheduleDaysCoherenceRule.php                                              not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  ⨯ src/Validation/Rules/RequiredFieldsRule.php                                                                                     not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/SchedulableConsistencyRule.php                                                not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/SchedulableValidationRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/ScheduleOverlapRule.php                                           braces_position, not_operator_with_successor_space, single_line_empty_body, ordered_imports  
  ⨯ src/Validation/Rules/TimeRangeRule.php                                                             not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/TimeSlotDateTimeRule.php                                                       single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Validator.php function_declaration, no_multiline_whitespace_around_double_arrow, concat_space, not_operator_with_successor_space, blank_line_before_statement, ord…  
  ⨯ src/helpers.php                 function_declaration, increment_style, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php                                                                class_attributes_separation, binary_operator_spaces  
  ⨯ tests/Integration/Traits/BelongsToSchedulableTest.php                              class_attributes_separation, single_line_comment_spacing, no_extra_blank_lines, ordered_imports  
  ⨯ tests/TestCase.php                                                                                                                                   concat_space, ordered_imports  
  ⨯ tests/Unit/Commands/CacheRulesCommandTest.php                                                                                                                    no_unused_imports  
  ⨯ tests/Unit/Domain/ModelMutationForbiddenTest.php                                                                                                                   ordered_imports  
  ⨯ tests/Unit/Domain/MutationContextAllowsMutationTest.php                                                                                                            ordered_imports  
  ⨯ tests/Unit/Domain/RepositoryMutationTest.php                                                             class_attributes_separation, trailing_comma_in_multiline, ordered_imports  
  ⨯ tests/Unit/HelpersTest.php                                                                                                                             class_attributes_separation  
  ⨯ tests/Unit/Models/AvailabilityTest.php                                                                                            phpdoc_separation, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ImpedimentTest.php                                                                                              phpdoc_separation, ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                                                               class_attributes_separation, no_extra_blank_lines  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                                                                                       single_quote  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php class_attributes_separation, increment_style, single_quote, concat_space, trailing_comma_in_multiline, no_extra_blank_lines, ordere…  
  ⨯ tests/Unit/Services/ScheduleServiceTest.php                                                                     class_attributes_separation, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php                                                                            new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                                             class_attributes_separation, new_with_parentheses, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityTemporalCoherenceRuleTest.php                                                          single_quote, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                                                                           new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php                                             single_quote, blank_line_after_namespace, no_extra_blank_lines  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  

