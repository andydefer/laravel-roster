# Pint Formatting Test Report
*Generated: mar. 23 déc. 2025 23:26:47 WAT*


  ..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯...⨯.⨯⨯⨯..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯....⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.....⨯...⨯.⨯⨯.............⨯....⨯⨯⨯.........⨯......⨯⨯...⨯......⨯.....⨯....

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ................................................................................................................................................ 142 files, 69 style issues  
  ⨯ config/roster.php                                                                                                                                             no_extra_blank_lines  
  ⨯ src/Commands/InstallRosterCommand.php                                                        unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                                                                          phpdoc_align  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                                                            phpdoc_align  
  ⨯ src/Contracts/Services/AvailabilityValidatorInterface.php                                                                                              class_attributes_separation  
  ⨯ src/Contracts/Services/ConfigurableInterface.php                                                                                         class_attributes_separation, phpdoc_align  
  ⨯ src/Contracts/Services/SlotFinderInterface.php                                                                                                         class_attributes_separation  
  ⨯ src/DTOs/AvailabilityData.php class_attributes_separation, function_declaration, braces_position, cast_spaces, not_operator_with_successor_space, single_line_empty_body, phpdoc_…  
  ⨯ src/DTOs/ImpedimentData.php                                                                            function_declaration, braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/ScheduleData.php                                                                                    function_declaration, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Enums/EntityType.php                                                                                                                                              concat_space  
  ⨯ src/Exceptions/Messages/ErrorMessageFactory.php                                                                                                         concat_space, phpdoc_align  
  ⨯ src/Exceptions/NotFoundException.php                                                                                        cast_spaces, blank_line_before_statement, phpdoc_align  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  ⨯ src/Models/Availability.php                                                                           trailing_comma_in_multiline, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Models/Impediment.php                                                                         class_attributes_separation, function_declaration, ordered_imports, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                                              ordered_imports, phpdoc_align  
  ⨯ src/Observers/SchedulableObserver.php                                                                                                                                 phpdoc_align  
  ⨯ src/Repositories/AbstractRepository.php                                                                                                                               phpdoc_align  
  ⨯ src/Repositories/AvailabilityRepository.php function_declaration, no_multiline_whitespace_around_double_arrow, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_…  
  ⨯ src/Repositories/ImpedimentRepository.php                                                                                    no_unused_imports, no_extra_blank_lines, phpdoc_align  
  ⨯ src/Repositories/ScheduleRepository.php                                                                                                         no_extra_blank_lines, phpdoc_align  
  ⨯ src/RosterServiceProvider.php                                                                                      class_attributes_separation, new_with_parentheses, concat_space  
  ⨯ src/Services/AvailabilityService.php        function_declaration, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Services/Core/AbstractAvailabilityValidatingService.php                                                                        not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Services/Core/AbstractEntityScopingService.php class_attributes_separation, unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement, phpdoc_ali…  
  ⨯ src/Services/Core/AbstractService.php                                                                       class_attributes_separation, blank_line_before_statement, phpdoc_align  
  ⨯ src/Services/Core/AbstractValidatingService.php                                                              not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  ⨯ src/Services/Core/AvailabilityChecker.php                                                braces_position, phpdoc_separation, single_line_empty_body, ordered_imports, phpdoc_align  
  ⨯ src/Services/Core/ResourcePublisherService.php             increment_style, concat_space, braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/Services/Core/SlotFinderService.php class_attributes_separation, function_declaration, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line…  
  ⨯ src/Services/ImpedimentService.php                           trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  ⨯ src/Services/ScheduleService.php trailing_comma_in_multiline, phpdoc_separation, cast_spaces, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, ph…  
  ⨯ src/Traits/BelongsToSchedulable.php                                                                                                                                ordered_imports  
  ⨯ src/Traits/DateRangeOverlapTrait.php                                                     unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Traits/FilterableTrait.php                                             concat_space, unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Context/ValidationContext.php                                                       function_declaration, not_operator_with_successor_space, binary_operator_spaces  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php                                                                                              concat_space, ordered_imports  
  ⨯ src/Validation/RuleScanner.php           new_with_parentheses, function_declaration, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AbstractRule.php                                                                                                                  blank_line_before_statement  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                  single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityDaysCoherenceRule.php                                             not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityOverlapRule.php                                                                                not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityOwnershipRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityTimeRangeRule.php                                                                              not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityTypeRule.php                                                             not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  ⨯ src/Validation/Rules/DaysValidationRule.php                                                        not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/DurationRule.php                                                 single_quote, concat_space, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/FutureDateRule.php                                                                                         not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/RequiredFieldsRule.php                                                                                     not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/SchedulableConsistencyRule.php                                                not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/SchedulableValidationRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/ScheduleOverlapRule.php                                 not_operator_with_successor_space, no_extra_blank_lines, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/TimeSlotDateTimeRule.php                                                       single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Validator.php function_declaration, no_multiline_whitespace_around_double_arrow, concat_space, not_operator_with_successor_space, blank_line_before_statement, ord…  
  ⨯ src/helpers.php                 function_declaration, increment_style, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php                                                                                                    ordered_imports  
  ⨯ tests/Support/TestSchedulable.php                                                                                                                      class_attributes_separation  
  ⨯ tests/TestCase.php                                                                                                                                   concat_space, ordered_imports  
  ⨯ tests/Unit/HelpersTest.php                                                                                                                             class_attributes_separation  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                                            class_attributes_separation, no_unused_imports, no_extra_blank_lines  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                                   class_attributes_separation, method_argument_space, single_quote  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php                                                                   class_attributes_separation, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Services/ScheduleServiceTest.php                                                  class_attributes_separation, no_unused_imports, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php                                                                            new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                                             class_attributes_separation, new_with_parentheses, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                                                                           new_with_parentheses, ordered_imports  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  

