# Pint Formatting Test Report
*Generated: sam. 27 déc. 2025 09:54:02 WAT*


  ..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯.....⨯⨯⨯⨯⨯.⨯.⨯⨯⨯....⨯....⨯.......⨯......⨯....⨯⨯⨯⨯⨯.........⨯....⨯..⨯⨯⨯⨯⨯..⨯...⨯⨯⨯.....⨯..⨯.

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ................................................................................................................................................ 152 files, 86 style issues  
  ⨯ database/migrations/2024_01_01_000001_create_roster_availabilities_table.php         class_definition, no_superfluous_phpdoc_tags, braces_position, phpdoc_trim, no_unused_imports  
  ⨯ src/Commands/CacheRulesCommand.php                                                                                         concat_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Commands/InstallRosterCommand.php                                                                                     class_attributes_separation, blank_line_before_statement  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                                                                          phpdoc_align  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                                           ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Repository/RepositoryInterface.php                                                                                                                      phpdoc_align  
  ⨯ src/Contracts/Repository/ScheduleRepositoryInterface.php                                                                                             ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Services/ServiceInterface.php                                                                                  no_superfluous_phpdoc_tags, phpdoc_trim, phpdoc_align  
  ⨯ src/Contracts/Validation/RuleInterface.php                                                                                                         phpdoc_separation, phpdoc_align  
  ⨯ src/Contracts/Validation/ValidationContextInterface.php                                                                                                               phpdoc_align  
  ⨯ src/Contracts/Validation/ValidatorInterface.php                                                                                                                       phpdoc_align  
  ⨯ src/DTOs/AbstractData.php                                                                       function_declaration, no_unused_imports, blank_line_before_statement, phpdoc_align  
  ⨯ src/DTOs/AvailabilityData.php                                class_attributes_separation, braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/DataInterface.php                                                                                                                         no_unused_imports, phpdoc_align  
  ⨯ src/DTOs/ImpedimentData.php                                                                                                  braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/ScheduleData.php                                                                                                    braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Domain/DTOs/CacheStats.php no_superfluous_phpdoc_tags, concat_space, unary_operator_spaces, braces_position, phpdoc_separation, not_operator_with_successor_space, single_lin…  
  ⨯ src/Domain/DTOs/ConflictResult.php                                                        braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/Domain/Helpers/TimeSlotHelper.php                   class_attributes_separation, not_operator_with_successor_space, no_extra_blank_lines, binary_operator_spaces, phpdoc_align  
  ⨯ src/Domain/Helpers/TimeWindowHelper.php                                                                         phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Domain/Services/CacheRulesService.php single_quote, unary_operator_spaces, braces_position, phpdoc_separation, not_operator_with_successor_space, single_line_empty_body, bla…  
  ⨯ src/Domain/Services/RosterInstallerService.php                                 unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement, phpdoc_align  
  ⨯ src/Domain/Services/TemporalConflictService.php function_declaration, no_multiline_whitespace_around_double_arrow, no_superfluous_phpdoc_tags, trailing_comma_in_multiline, brace…  
  ⨯ src/Enums/EntityType.php                                                                                                                                              concat_space  
  ⨯ src/Exceptions/InvalidServiceContextException.php                                            no_multiline_whitespace_around_double_arrow, single_quote, concat_space, phpdoc_align  
  ⨯ src/Exceptions/MergeConflictException.php                                                                                                               concat_space, phpdoc_align  
  ⨯ src/Exceptions/MissingOwnerException.php                                                                                                               blank_line_before_statement  
  ⨯ src/Exceptions/NotFoundException.php                                                                                        cast_spaces, blank_line_before_statement, phpdoc_align  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  ⨯ src/Models/Availability.php class_attributes_separation, no_superfluous_phpdoc_tags, unary_operator_spaces, phpdoc_trim, not_operator_with_successor_space, blank_line_before_sta…  
  ⨯ src/Models/Impediment.php                                                                                          function_declaration, blank_line_before_statement, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                 blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ src/Observers/EnforceDomainMutationObserver.php                                                                                                  not_operator_with_successor_space  
  ⨯ src/Repositories/AbstractRepository.php no_multiline_whitespace_around_double_arrow, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_import…  
  ⨯ src/Repositories/AvailabilityRepository.php                                        trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/RosterServiceProvider.php                                                                                                no_superfluous_phpdoc_tags, concat_space, phpdoc_trim  
  ⨯ src/Services/AvailabilityService.php            class_attributes_separation, trailing_comma_in_multiline, not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  ⨯ src/Services/Core/AbstractService.php class_attributes_separation, concat_space, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, no_extra_blan…  
  ⨯ src/Services/ImpedimentService.php                              trailing_comma_in_multiline, phpdoc_separation, no_unused_imports, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/ScheduleService.php                                                      trailing_comma_in_multiline, cast_spaces, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Support/RosterMutationContext.php                                                                                                 increment_style, blank_line_before_statement  
  ⨯ src/Support/RosterServiceContext.php                                                                                                  increment_style, blank_line_before_statement  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Cache/RuleCacheGenerator.php function_declaration, blank_line_after_opening_tag, concat_space, not_operator_with_successor_space, blank_line_before_statement, ord…  
  ⨯ src/Validation/Context/ValidationContext.php               function_declaration, concat_space, phpdoc_separation, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php                                                                                              concat_space, ordered_imports  
  ⨯ src/Validation/RuleScanner.php new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, blank…  
  ⨯ src/Validation/Rules/AbstractRule.php                                                                                                                  blank_line_before_statement  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                                not_operator_with_successor_space, ordered_imports, phpdoc_align  
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
  ⨯ src/Validation/Validator.php                                                function_declaration, concat_space, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/helpers.php function_declaration, no_multiline_whitespace_around_double_arrow, no_superfluous_phpdoc_tags, concat_space, phpdoc_separation, not_operator_with_successor_space…  
  ⨯ tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php                                                                            no_unused_imports, no_extra_blank_lines  
  ⨯ tests/Integration/Traits/BelongsToSchedulableTest.php                                                                                 class_attributes_separation, ordered_imports  
  ⨯ tests/TestCase.php                                                                                                                                   concat_space, ordered_imports  
  ⨯ tests/Unit/Commands/CacheRulesCommandTest.php                                                                                                      concat_space, no_unused_imports  
  ⨯ tests/Unit/Domain/ModelMutationForbiddenTest.php                                                                                                                   ordered_imports  
  ⨯ tests/Unit/Domain/MutationContextAllowsMutationTest.php                                                                                                            ordered_imports  
  ⨯ tests/Unit/Domain/RepositoryMutationTest.php                                                                                                    no_unused_imports, ordered_imports  
  ⨯ tests/Unit/HelpersTest.php                                                                                                                             class_attributes_separation  
  ⨯ tests/Unit/Models/AvailabilityTest.php                                                                                                               ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ImpedimentTest.php                                                                                                                 ordered_imports, phpdoc_align  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                                                               class_attributes_separation, no_extra_blank_lines  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                                                                                       single_quote  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php       class_attributes_separation, increment_style, single_quote, concat_space, cast_spaces, ordered_imports, binary_operator_spaces  
  ⨯ tests/Unit/Services/ScheduleServiceTest.php                                                                                                     no_unused_imports, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php                                                                            new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                                             class_attributes_separation, new_with_parentheses, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityTemporalCoherenceRuleTest.php                                                                                                 single_quote  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                                                                           new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php                                                                              single_quote, ordered_imports  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  

