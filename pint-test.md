# Pint Formatting Test Report
*Generated: jeu. 25 déc. 2025 16:13:45 WAT*


  ..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯...⨯⨯⨯⨯⨯⨯⨯⨯⨯.⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯.....⨯⨯⨯⨯⨯⨯⨯⨯⨯......⨯...⨯⨯⨯.......⨯...........⨯⨯⨯⨯.........⨯......⨯⨯⨯⨯⨯⨯...⨯......⨯.....⨯....

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ................................................................................................................................................ 156 files, 83 style issues  
  ⨯ config/roster-validation.php                                                                                                                                  no_extra_blank_lines  
  ⨯ config/roster.php                                                                                                                                             no_extra_blank_lines  
  ⨯ src/Commands/CacheRulesCommand.php       increment_style, single_quote, blank_line_after_opening_tag, concat_space, not_operator_with_successor_space, blank_line_before_statement  
  ⨯ src/Commands/InstallRosterCommand.php                                                        unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement  
  ⨯ src/Contracts/EntityServiceInterface.php                                                                                                        no_extra_blank_lines, phpdoc_align  
  ⨯ src/Contracts/Filters/FilterableInterface.php                                                                                                          class_attributes_separation  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                          class_attributes_separation, no_unused_imports, phpdoc_align  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                        no_unused_imports, ordered_imports, phpdoc_align  
  ⨯ src/Contracts/Repository/ScheduleRepositoryInterface.php                                                                            class_attributes_separation, no_unused_imports  
  ⨯ src/Contracts/RepositoryInterface.php                                                                                                           no_extra_blank_lines, phpdoc_align  
  ⨯ src/Contracts/RosterDataInterface.php                                                        class_definition, braces_position, single_line_empty_body, blank_line_after_namespace  
  ⨯ src/Contracts/Services/AvailabilityCheckerInterface.php                                                                                                class_attributes_separation  
  ⨯ src/Contracts/Services/AvailabilityValidatorInterface.php                                                                                              class_attributes_separation  
  ⨯ src/Contracts/Services/ConfigurableInterface.php                                                                                         class_attributes_separation, phpdoc_align  
  ⨯ src/Contracts/Services/SlotFinderInterface.php                                                                                                         class_attributes_separation  
  ⨯ src/DTOs/AvailabilityData.php class_attributes_separation, function_declaration, braces_position, cast_spaces, not_operator_with_successor_space, single_line_empty_body, no_extr…  
  ⨯ src/DTOs/ImpedimentData.php                                                                            function_declaration, braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/DTOs/ScheduleData.php                                                                   function_declaration, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Enums/EntityType.php                                                                                                                                              concat_space  
  ⨯ src/Exceptions/InvalidServiceContextException.php                                                                                                       single_quote, concat_space  
  ⨯ src/Exceptions/MergeConflictException.php                                                                                                               concat_space, phpdoc_align  
  ⨯ src/Exceptions/Messages/ErrorMessageFactory.php                                                                                                         concat_space, phpdoc_align  
  ⨯ src/Exceptions/NotFoundException.php                                                                                        cast_spaces, blank_line_before_statement, phpdoc_align  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  ⨯ src/Models/Availability.php                                              class_attributes_separation, trailing_comma_in_multiline, not_operator_with_successor_space, phpdoc_align  
  ⨯ src/Models/Impediment.php                                                                         class_attributes_separation, function_declaration, ordered_imports, phpdoc_align  
  ⨯ src/Models/Schedule.php                                                                                                                              ordered_imports, phpdoc_align  
  ⨯ src/Observers/EnforceDomainMutationObserver.php                                                                                                  not_operator_with_successor_space  
  ⨯ src/Repositories/AbstractRepository.php class_attributes_separation, function_declaration, lambda_not_used_import, spaces_inside_parentheses, concat_space, not_operator_with_suc…  
  ⨯ src/Repositories/AvailabilityRepository.php class_attributes_separation, no_multiline_whitespace_around_double_arrow, trailing_comma_in_multiline, unary_operator_spaces, phpdoc_…  
  ⨯ src/Repositories/ImpedimentRepository.php                                                                          class_attributes_separation, no_extra_blank_lines, phpdoc_align  
  ⨯ src/Repositories/ScheduleRepository.php                                                                            class_attributes_separation, no_extra_blank_lines, phpdoc_align  
  ⨯ src/RosterServiceProvider.php                                                                                                  new_with_parentheses, concat_space, ordered_imports  
  ⨯ src/Services/AvailabilityMergeService.php function_declaration, trailing_comma_in_multiline, braces_position, phpdoc_order, phpdoc_separation, not_operator_with_successor_space,…  
  ⨯ src/Services/AvailabilityService.php class_attributes_separation, trailing_comma_in_multiline, unary_operator_spaces, phpdoc_separation, no_unused_imports, not_operator_with_suc…  
  ⨯ src/Services/Core/AbstractAvailabilityValidatingService.php                                      class_attributes_separation, no_unused_imports, not_operator_with_successor_space  
  ⨯ src/Services/Core/AbstractEntityScopingService.php class_attributes_separation, unary_operator_spaces, no_unused_imports, not_operator_with_successor_space, blank_line_before_st…  
  ⨯ src/Services/Core/AbstractService.php class_attributes_separation, concat_space, no_unused_imports, not_operator_with_successor_space, blank_line_before_statement, ordered_impor…  
  ⨯ src/Services/Core/AbstractValidatingService.php                                           no_unused_imports, not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  ⨯ src/Services/Core/ResourcePublisherService.php             increment_style, concat_space, braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  ⨯ src/Services/Core/SlotFinderService.php class_attributes_separation, function_declaration, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line…  
  ⨯ src/Services/ImpedimentService.php class_attributes_separation, trailing_comma_in_multiline, phpdoc_separation, no_unused_imports, not_operator_with_successor_space, no_extra_bl…  
  ⨯ src/Services/ScheduleService.php class_attributes_separation, trailing_comma_in_multiline, phpdoc_separation, cast_spaces, not_operator_with_successor_space, no_extra_blank_line…  
  ⨯ src/Support/RosterMutationContext.php                                                                                                 increment_style, blank_line_before_statement  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  ⨯ src/Validation/Cache/RuleCacheGenerator.php function_declaration, blank_line_after_opening_tag, concat_space, not_operator_with_successor_space, blank_line_before_statement, ord…  
  ⨯ src/Validation/Context/ValidationContext.php                                                       function_declaration, not_operator_with_successor_space, binary_operator_spaces  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php                                                                                              concat_space, ordered_imports  
  ⨯ src/Validation/RuleScanner.php class_attributes_separation, new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, not_operator_with_successor_sp…  
  ⨯ src/Validation/Rules/AbstractRule.php                                                                                                                  blank_line_before_statement  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                  single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityDaysCoherenceRule.php                                             not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityOverlapRule.php                                                          not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityOwnershipRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityTimeRangeRule.php                                                 class_attributes_separation, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/AvailabilityTypeRule.php                                                             not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  ⨯ src/Validation/Rules/DaysValidationRule.php                                                        not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/DurationRule.php                                                 single_quote, concat_space, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  ⨯ src/Validation/Rules/FutureDateRule.php                                                                                         not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/NoDangerousMergeRule.php                                                                                   not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/RequiredFieldsRule.php                                                                                     not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Rules/SchedulableConsistencyRule.php                                                not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/SchedulableValidationRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/ScheduleOverlapRule.php                                 not_operator_with_successor_space, no_extra_blank_lines, blank_line_before_statement, ordered_imports  
  ⨯ src/Validation/Rules/TimeSlotDateTimeRule.php                                                       single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  ⨯ src/Validation/Validator.php function_declaration, no_multiline_whitespace_around_double_arrow, concat_space, not_operator_with_successor_space, blank_line_before_statement, ord…  
  ⨯ src/helpers.php                 function_declaration, increment_style, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
  ⨯ tests/Integration/Traits/BelongsToSchedulableTest.php           class_attributes_separation, single_line_comment_spacing, no_unused_imports, no_extra_blank_lines, ordered_imports  
  ⨯ tests/TestCase.php                                                                                                                                   concat_space, ordered_imports  
  ⨯ tests/Unit/Domain/ModelMutationForbiddenTest.php                                                                                                                   ordered_imports  
  ⨯ tests/Unit/Domain/MutationContextAllowsMutationTest.php                                                                                                            ordered_imports  
  ⨯ tests/Unit/Domain/RepositoryMutationTest.php                                                                    class_attributes_separation, new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/HelpersTest.php                                                                                                                             class_attributes_separation  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                                                               class_attributes_separation, no_extra_blank_lines  
  ⨯ tests/Unit/Services/AvailabilityMergeServiceTest.php                                                                                                               ordered_imports  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                                                                 single_quote, no_extra_blank_lines  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php class_attributes_separation, single_quote, concat_space, trailing_comma_in_multiline, no_unused_imports, no_extra_blank_lines, orde…  
  ⨯ tests/Unit/Services/ScheduleServiceTest.php                                                                     class_attributes_separation, no_extra_blank_lines, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php                                                                            new_with_parentheses, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                                             class_attributes_separation, new_with_parentheses, single_quote, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                           class_attributes_separation, new_with_parentheses, no_unused_imports, ordered_imports  
  ⨯ tests/Unit/Validation/Rules/NoDangerousMergeRuleTest.php                                                                              class_attributes_separation, ordered_imports  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  

