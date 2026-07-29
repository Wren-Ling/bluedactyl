import { Box, ChevronDown, TriangleAlert } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import isEqual from 'react-fast-compare';
import { toast } from 'sonner';
import { Trans, useTranslation } from 'react-i18next';
import i18n from '@/i18n/config';

import { Button } from '@/components/ui/button';
import FlashMessageRender from '@/components/FlashMessageRender';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import Spinner from '@/components/elements/Spinner';
import { Switch } from '@/components/ui/switch';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import OperationProgressModal from '@/components/server/operations/OperationProgressModal';
import WingsOperationProgressModal from '@/components/server/operations/WingsOperationProgressModal';

import { httpErrorToHuman } from '@/api/http';
import getNests from '@/api/nests/getNests';
import applyEggChange from '@/api/server/applyEggChange';
import applyEggChangeSync from '@/api/server/applyEggChangeSync';
import { getGlobalDaemonType } from '@/api/server/getServer';
import previewEggChange, { EggPreview } from '@/api/server/previewEggChange';
import { ServerOperation } from '@/api/server/serverOperations';
import getServerBackups from '@/api/swr/getServerBackups';
import getServerStartup from '@/api/swr/getServerStartup';

import { ServerContext } from '@/state/server';

import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';

interface Egg {
    object: string;
    attributes: {
        id: number;
        uuid: string;
        name: string;
        description: string;
    };
}

interface Nest {
    object: string;
    attributes: {
        id: number;
        uuid: string;
        author: string;
        name: string;
        description: string;
        created_at: string;
        updated_at: string;
        relationships: {
            eggs: {
                object: string;
                data: Egg[];
            };
        };
    };
}

const MAX_DESCRIPTION_LENGTH = 150;
const hidden_nest_prefix = '!';
const blank_egg_prefix = '@';

type FlowStep = 'overview' | 'select-game' | 'select-software' | 'configure' | 'review';

// Laravel-style validation function
const validateEnvironmentVariables = (variables: any[], pendingVariables: Record<string, string>): string[] => {
    const errors: string[] = [];

    variables.forEach((variable) => {
        if (!variable.user_editable) return; // Skip non-editable variables

        const value = pendingVariables[variable.env_variable] || '';
        const rules = variable.rules || '';
        const ruleArray = rules
            .split('|')
            .map((rule) => rule.trim())
            .filter((rule) => rule.length > 0);

        // Check if variable is required (backend automatically adds nullable if not present)
        const isRequired = ruleArray.includes('required');
        const isNullable = ruleArray.includes('nullable') || !isRequired;

        // If required and empty/null
        if (isRequired && (!value || value.trim() === '')) {
            errors.push(i18n.t('shell:var_required', { name: variable.name }));
            return;
        }

        // If nullable and empty, skip other validations
        if (isNullable && (!value || value.trim() === '')) {
            return;
        }

        // Validate each rule
        ruleArray.forEach((rule) => {
            const [ruleName, ruleValue] = rule.split(':');

            switch (ruleName) {
                case 'string':
                    if (typeof value !== 'string') {
                        errors.push(i18n.t('shell:var_must_be_string', { name: variable.name }));
                    }
                    break;

                case 'integer':
                case 'numeric':
                    if (value && isNaN(Number(value))) {
                        errors.push(i18n.t('shell:var_must_be_number', { name: variable.name }));
                    }
                    break;

                case 'boolean': {
                    const boolValues = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'];
                    if (value && !boolValues.includes(value.toLowerCase())) {
                        errors.push(i18n.t('shell:var_must_be_boolean', { name: variable.name }));
                    }
                    break;
                }

                case 'min': {
                    if (ruleValue && value) {
                        const minValue = parseInt(ruleValue);
                        if (value.length < minValue) {
                            errors.push(i18n.t('shell:var_min_chars', { name: variable.name, min: minValue }));
                        }
                    }
                    break;
                }

                case 'max': {
                    if (ruleValue && value) {
                        const maxValue = parseInt(ruleValue);
                        if (value.length > maxValue) {
                            errors.push(i18n.t('shell:var_max_chars', { name: variable.name, max: maxValue }));
                        }
                    }
                    break;
                }

                case 'between': {
                    if (ruleValue && value) {
                        const [min, max] = ruleValue.split(',').map((v) => parseInt(v.trim()));
                        if (value.length < min || value.length > max) {
                            errors.push(i18n.t('shell:var_between_chars', { name: variable.name, min, max }));
                        }
                    }
                    break;
                }

                case 'in': {
                    if (ruleValue && value) {
                        const allowedValues = ruleValue.split(',').map((v) => v.trim());
                        if (!allowedValues.includes(value)) {
                            errors.push(i18n.t('shell:var_must_be_one_of', { name: variable.name, values: allowedValues.join(', ') }));
                        }
                    }
                    break;
                }

                case 'regex': {
                    if (ruleValue && value) {
                        try {
                            // Handle Laravel regex format: regex:/pattern/flags
                            const regexMatch = ruleValue.match(/^\/(.+)\/([gimuy]*)$/);
                            if (regexMatch) {
                                const regex = new RegExp(regexMatch[1], regexMatch[2]);
                                if (!regex.test(value)) {
                                    errors.push(i18n.t('shell:var_format_invalid', { name: variable.name }));
                                }
                            }
                        } catch (e) {
                            // Invalid regex - skip validation
                        }
                    }
                    break;
                }

                case 'alpha':
                    if (value && !/^[a-zA-Z]+$/.test(value)) {
                        errors.push(i18n.t('shell:var_letters_only', { name: variable.name }));
                    }
                    break;

                case 'alpha_num':
                    if (value && !/^[a-zA-Z0-9]+$/.test(value)) {
                        errors.push(i18n.t('shell:var_letters_numbers', { name: variable.name }));
                    }
                    break;

                case 'alpha_dash':
                    if (value && !/^[a-zA-Z0-9_-]+$/.test(value)) {
                        errors.push(i18n.t('shell:var_alpha_dash', { name: variable.name }));
                    }
                    break;

                case 'url':
                    if (value) {
                        try {
                            new URL(value);
                        } catch {
                            errors.push(i18n.t('shell:var_valid_url', { name: variable.name }));
                        }
                    }
                    break;

                case 'email':
                    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        errors.push(i18n.t('shell:var_valid_email', { name: variable.name }));
                    }
                    break;

                case 'ip': {
                    if (value) {
                        const ipRegex =
                            /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                        if (!ipRegex.test(value)) {
                            errors.push(i18n.t('shell:var_valid_ip', { name: variable.name }));
                        }
                    }
                    break;
                }

                // Skip validation rules that don't apply to frontend
                case 'required':
                case 'nullable':
                case 'sometimes':
                    break;

                default:
                    // Unknown rule - log for debugging but don't error
                    if (
                        process.env.NODE_ENV === 'development' &&
                        !['string', 'array', 'file', 'image'].includes(ruleName)
                    ) {
                        console.warn(`Unknown validation rule: ${ruleName} for variable ${variable.name}`);
                    }
                    break;
            }
        });
    });

    return errors;
};

const SoftwareContainer = () => {
    const { t } = useTranslation();
    const serverData = ServerContext.useStoreState((state) => state.server.data);
    const daemonType = getGlobalDaemonType();
    const uuid = serverData?.uuid;
    const [nests, setNests] = useState<Nest[]>();
    //const eggs = nests?.reduce(
    //    (eggArray, nest) => [...eggArray, ...nest.attributes.relationships.eggs.data],
    //    [] as Egg[],
    //);
    const currentEgg = serverData?.egg;
    //const originalEgg = currentEgg;
    const currentEggName = useMemo(() => {
        // Don't attempt calculation until both nests data and currentEgg are available
        if (!nests || !currentEgg) {
            return undefined;
        }

        const foundNest = nests.find((nest) =>
            nest?.attributes?.relationships?.eggs?.data?.find((egg) => egg?.attributes?.uuid === currentEgg),
        );

        return foundNest?.attributes?.relationships?.eggs?.data?.find((egg) => egg?.attributes?.uuid === currentEgg)
            ?.attributes?.name;
    }, [nests, currentEgg]);
    const backupLimit = serverData?.featureLimits.backups;

    const { data: backups } = getServerBackups();
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);

    // Flow state
    const [currentStep, setCurrentStep] = useState<FlowStep>('overview');
    const [isLoading, setIsLoading] = useState(false);
    const [selectedNest, setSelectedNest] = useState<Nest | null>(null);
    const [selectedEgg, setSelectedEgg] = useState<Egg | null>(null);
    const [eggPreview, setEggPreview] = useState<EggPreview | null>(null);
    const [pendingVariables, setPendingVariables] = useState<Record<string, string>>({});
    const [variableErrors, setVariableErrors] = useState<Record<string, string>>({});
    const [currentOperationId, setCurrentOperationId] = useState<string | null>(null);
    const [showOperationModal, setShowOperationModal] = useState(false);
    const [showWipeConfirmation, setShowWipeConfirmation] = useState(false);
    const [wipeCountdown, setWipeCountdown] = useState(5);
    const [wipeLoading, setWipeLoading] = useState(false);
    const [shiftPressed, setShiftPressed] = useState(false);

    // Configuration options
    const [shouldBackup, setShouldBackup] = useState(false);
    const [shouldWipe, setShouldWipe] = useState(false);
    const [showFullDescriptions, setShowFullDescriptions] = useState<Record<string, boolean>>({});

    // Startup and Docker configuration
    const [customStartup, setCustomStartup] = useState('');
    const [selectedDockerImage, setSelectedDockerImage] = useState('');

    // Data loading
    useEffect(() => {
        const fetchData = async () => {
            const data = await getNests();
            setNests(data);
        };
        fetchData();
    }, []);

    const variables = ServerContext.useStoreState(
        ({ server }) => ({
            variables: server.data?.variables || [],
            invocation: server.data?.invocation || '',
            dockerImage: server.data?.dockerImage || '',
        }),
        isEqual,
    );

    const { data, mutate } = getServerStartup(uuid || '', {
        ...variables,
        dockerImages: { [variables.dockerImage]: variables.dockerImage },
        rawStartupCommand: variables.invocation,
    });

    useDeepCompareEffect(() => {
        if (!data) return;
        setServerFromState((s) => ({
            ...s,
            invocation: data.invocation,
            variables: data.variables,
        }));
    }, [data]);

    // Initialize backup setting based on limits
    useEffect(() => {
        if (backups) {
            // null = unlimited, 0 = disabled, positive number = cap
            setShouldBackup(backupLimit !== 0 && (backupLimit === null || backups.backupCount < backupLimit));
        }
    }, [backups, backupLimit]);

    // Countdown effect for wipe confirmation modal
    useEffect(() => {
        let interval: NodeJS.Timeout;
        if (showWipeConfirmation && wipeCountdown > 0) {
            interval = setInterval(() => {
                setWipeCountdown((prev) => prev - 1);
            }, 1000);
        }
        return () => {
            if (interval) clearInterval(interval);
        };
    }, [showWipeConfirmation, wipeCountdown]);

    // Reset countdown when wipe confirmation modal opens
    useEffect(() => {
        if (showWipeConfirmation) {
            setWipeCountdown(5);
        }
    }, [showWipeConfirmation]);

    const handleKeyDown = (event) => {
        if (event.shiftKey) setShiftPressed(true);
    };

    const handleKeyUp = (event) => {
        if (!event.shiftKey) setShiftPressed(false);
    };

    useEffect(() => {
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('keyup', handleKeyUp);

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.removeEventListener('keyup', handleKeyUp);
        };
    });

    // Flow control functions
    const resetFlow = () => {
        setCurrentStep('overview');
        setSelectedNest(null);
        setSelectedEgg(null);
        setEggPreview(null);
        setPendingVariables({});
        setVariableErrors({});
        setShouldBackup(backupLimit !== 0 && (backupLimit === null || (backups?.backupCount || 0) < backupLimit));
        setShouldWipe(false);
        setCustomStartup('');
        setSelectedDockerImage('');
    };

    const handleNestSelection = (nest: Nest) => {
        setSelectedNest(nest);
        setSelectedEgg(null);
        setEggPreview(null);
        setPendingVariables({});
        setVariableErrors({});
        setCustomStartup('');
        setSelectedDockerImage('');
        setCurrentStep('select-software');
    };

    const handleEggSelection = async (egg: Egg) => {
        if (!selectedNest || !uuid) return;

        setIsLoading(true);
        setSelectedEgg(egg);

        try {
            const preview = await previewEggChange(uuid, egg.attributes.id, selectedNest.attributes.id);
            setEggPreview(preview);

            // Check for subdomain compatibility warnings
            if (preview.warnings && preview.warnings.length > 0) {
                const subdomainWarning = preview.warnings.find((w) => w.type === 'subdomain_incompatible');
                if (subdomainWarning) {
                    toast.error(subdomainWarning.message, {
                        duration: 8000,
                        dismissible: true,
                    });
                }
            }

            // Initialize variables with current values or defaults
            const initialVariables: Record<string, string> = {};
            preview.variables.forEach((variable) => {
                const existingVar = data?.variables.find((v) => v.envVariable === variable.env_variable);
                initialVariables[variable.env_variable] = existingVar?.serverValue || variable.default_value || '';
            });
            setPendingVariables(initialVariables);

            // Set default startup command and docker image
            setCustomStartup(preview.egg.startup);

            // Automatically select the default docker image if available
            // Backend returns: {"Display Name": "actual/image:tag"}
            const availableDisplayNames = Object.keys(preview.docker_images || {});
            if (preview.default_docker_image && availableDisplayNames.includes(preview.default_docker_image)) {
                setSelectedDockerImage(preview.default_docker_image);
            } else if (availableDisplayNames.length > 0 && availableDisplayNames[0]) {
                setSelectedDockerImage(availableDisplayNames[0]);
            }

            setCurrentStep('configure');
        } catch (error) {
            console.error(error);
            toast.error(httpErrorToHuman(error));
        } finally {
            setIsLoading(false);
        }
    };

    const handleVariableChange = (envVariable: string, value: string) => {
        setPendingVariables((prev) => ({ ...prev, [envVariable]: value }));

        // Validate this specific variable in real-time and update errors
        if (eggPreview) {
            const variable = eggPreview.variables.find((v) => v.env_variable === envVariable);
            if (variable) {
                const errors = validateEnvironmentVariables([variable], { [envVariable]: value });
                setVariableErrors((prev) => {
                    const newErrors = { ...prev };
                    if (errors.length > 0 && errors[0]) {
                        newErrors[envVariable] = errors[0];
                    } else {
                        delete newErrors[envVariable];
                    }
                    return newErrors;
                });
            }
        }
    };

    const proceedToReview = () => {
        setCurrentStep('review');
    };

    const applyChanges = async () => {
        if (!selectedEgg || !selectedNest || !eggPreview) return;

        // Show final confirmation if wipe files is selected without backup
        if (shouldWipe && !shouldBackup) {
            setShowWipeConfirmation(true);
            return;
        }

        // Proceed with the operation
        executeApplyChanges();
    };

    const executeApplyChanges = async () => {
        if (!selectedEgg || !selectedNest || !eggPreview || !uuid) return;

        setIsLoading(true);

        try {
            // Validate all variables using Laravel-style validation rules
            const validationErrors = validateEnvironmentVariables(eggPreview.variables, pendingVariables);

            if (validationErrors.length > 0) {
                throw new Error(`Validation failed:\n${validationErrors.join('\n')}`);
            }

            // Convert display name back to actual image for backend
            const actualDockerImage =
                selectedDockerImage && eggPreview.docker_images
                    ? eggPreview.docker_images[selectedDockerImage]
                    : eggPreview.default_docker_image && eggPreview.docker_images
                        ? eggPreview.docker_images[eggPreview.default_docker_image]
                        : '';

            // Filter out empty environment variables to prevent validation issues
            const filteredEnvironment: Record<string, string> = {};
            Object.entries(pendingVariables).forEach(([key, value]) => {
                if (value && value.trim() !== '') {
                    filteredEnvironment[key] = value;
                }
            });

            if (daemonType?.toLowerCase() == 'elytra') {
                const response = await applyEggChange(uuid, {
                    egg_id: selectedEgg.attributes.id,
                    nest_id: selectedNest.attributes.id,
                    docker_image: actualDockerImage,
                    startup_command: customStartup,
                    environment: filteredEnvironment,
                    should_backup: shouldBackup,
                    should_wipe: shouldWipe,
                });

                setCurrentOperationId(response.operation_id);

                setShowOperationModal(true);
            } else if (daemonType?.toLowerCase() == 'wings') {
                await applyEggChangeSync(uuid, {
                    egg_id: selectedEgg.attributes.id,
                    nest_id: selectedNest.attributes.id,
                    docker_image: actualDockerImage,
                    startup_command: customStartup,
                    environment: filteredEnvironment,
                    should_backup: shouldBackup,
                    should_wipe: shouldWipe,
                });
            }

            toast.success(t('shell:software_change_success'));

            resetFlow();
        } catch (error) {
            console.error('Failed to start egg change operation:', error);
            toast.error(httpErrorToHuman(error));
        } finally {
            setIsLoading(false);
        }
    };

    const handleWipeConfirm = () => {
        setShowWipeConfirmation(false);
        setWipeLoading(true);
        executeApplyChanges().finally(() => setWipeLoading(false));
    };

    const handleOperationComplete = (operation: ServerOperation) => {
        if (operation.is_completed) {
            toast.success(t('shell:software_config_applied'));

            // Refresh server data to reflect changes
            mutate();
        } else if (operation.has_failed) {
            toast.error(operation.message || t('shell:software_change_failed'));
        }
    };

    const handleOperationError = (error: Error) => {
        toast.error(error.message || t('shell:operation_error'));
    };

    const closeOperationModal = () => {
        setShowOperationModal(false);
        setCurrentOperationId(null);
    };

    const toggleDescription = (id: string) => {
        setShowFullDescriptions((prev) => ({ ...prev, [id]: !prev[id] }));
    };

    const renderDescription = (description: string, id: string) => {
        const isLong = description.length > MAX_DESCRIPTION_LENGTH;
        const showFull = showFullDescriptions[id];

        return (
            <p className='text-sm text-muted-foreground leading-relaxed'>
                {isLong && !showFull ? (
                    <>
                        {description.slice(0, MAX_DESCRIPTION_LENGTH)}...{' '}
                        <button
                            onClick={() => toggleDescription(id)}
                            className='text-primary hover:underline font-medium'
                        >
                            {t('shell:show_more')}
                        </button>
                    </>
                ) : (
                    <>
                        {description}
                        {isLong && (
                            <>
                                {' '}
                                <button
                                    onClick={() => toggleDescription(id)}
                                    className='text-primary hover:underline font-medium'
                                >
                                    {t('shell:show_less')}
                                </button>
                            </>
                        )}
                    </>
                )}
            </p>
        );
    };

    const renderOverview = () => (
        <Card>
            <CardHeader>
                                <CardTitle className='text-xl font-extrabold tracking-tight'>{t('shell:current_software')}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className='flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4'>
                    <div className='flex items-center gap-3 sm:gap-4 min-w-0 flex-1'>
                        <div className='w-10 h-10 sm:w-12 sm:h-12 bg-muted/20 rounded-lg flex items-center justify-center flex-shrink-0'>
                            <Box className='w-5 h-5 sm:w-6 sm:h-6 text-muted-foreground' />
                        </div>
                        <div className='min-w-0 flex-1'>
                            {currentEggName ? (
                                currentEggName.includes(blank_egg_prefix) ? (
                                    <p className='text-amber-400 font-medium text-sm sm:text-base'>{t('shell:no_software_selected')}</p>
                                ) : (
                                    <p className='text-foreground font-medium text-sm sm:text-base truncate'>
                                        {currentEggName}
                                    </p>
                                )
                            ) : (
                                <div className='flex items-center gap-2'>
                                    <Spinner size='small' />
                                    <span className='text-muted-foreground text-sm'>{t('shell:loading')}</span>
                                </div>
                            )}
                            <p className='text-xs sm:text-sm text-muted-foreground leading-relaxed'>
                                {t('shell:current_software_desc')}
                            </p>
                        </div>
                    </div>
                    <div className='flex-shrink-0 w-full sm:w-auto'>
                        <Button
                            variant='default'
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                try {
                                    setCurrentStep('select-game');
                                } catch (error) {
                                    console.error('Error in change software click:', error);
                                }
                            }}
                            className='w-full sm:w-auto'
                            disabled={isLoading}
                        >
                            {isLoading && <Spinner size='small' />}
                            {t('shell:change_software')}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    );

    const renderGameSelection = () => (
        <Card>
            <CardHeader>
                <CardTitle className='text-xl font-extrabold tracking-tight'>{t('shell:select_category')}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className='space-y-4'>
                    <p className='text-sm text-muted-foreground'>{t('shell:choose_game_type')}</p>

                    <div className='grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4'>
                        {nests?.map((nest) =>
                            nest?.attributes?.name?.includes(hidden_nest_prefix) ? null : (
                                <button
                                    key={nest?.attributes?.uuid}
                                    onClick={() => handleNestSelection(nest)}
                                    className='p-4 sm:p-5 bg-muted/30 border border-border/20 rounded-lg hover:border-white/10 transition-all text-left active:bg-white/5 touch-manipulation'
                                >
                                    <h3 className='font-semibold text-foreground mb-2 text-base sm:text-lg'>
                                        {nest?.attributes?.name}
                                    </h3>
                                    {renderDescription(
                                        nest?.attributes?.description || '',
                                        `nest-${nest?.attributes?.uuid}`,
                                    )}
                                </button>
                            ),
                        )}
                    </div>

                    <div className='flex justify-center pt-4'>
                        <Button
                            variant='outline'
                            onClick={() => setCurrentStep('overview')}
                            className='w-full sm:w-auto'
                        >
                            {t('shell:back_to_overview')}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    );

    const renderSoftwareSelection = () => (
        <Card>
            <CardHeader>
                <CardTitle className='text-xl font-extrabold tracking-tight'>{t('shell:select_software')} - {selectedNest?.attributes.name}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className='space-y-4'>
                    <p className='text-sm text-muted-foreground'>{t('shell:choose_software_version')}</p>

                    {isLoading ? (
                        <div className='flex items-center justify-center py-16'>
                            <div className='flex flex-col items-center text-center'>
                                <Spinner size='large' />
                                <p className='text-muted-foreground mt-4'>{t('shell:loading_software_options')}</p>
                            </div>
                        </div>
                    ) : (
                        <div className='grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4'>
                            {selectedNest?.attributes?.relationships?.eggs?.data?.map((egg) => (
                                <button
                                    key={egg.attributes.uuid}
                                    onClick={() => handleEggSelection(egg)}
                                    disabled={isLoading}
                                    className='p-4 bg-muted/30 border border-border/20 rounded-lg hover:border-white/10 transition-all text-left touch-manipulation disabled:opacity-50 disabled:cursor-not-allowed'
                                >
                                    <div className='flex items-center gap-2 mb-2'>
                                        {isLoading && selectedEgg?.attributes?.uuid === egg?.attributes?.uuid && (
                                            <Spinner size='small' />
                                        )}
                                        <h3 className='font-semibold text-foreground text-sm sm:text-base'>
                                            {egg?.attributes?.name}
                                        </h3>
                                    </div>
                                    {renderDescription(egg?.attributes?.description || '', `egg-${egg?.attributes?.uuid}`)}
                                </button>
                            ))}
                        </div>
                    )}

                    <div className='flex flex-col sm:flex-row justify-center gap-3 pt-4'>
                        <Button
                            variant='outline'
                            onClick={() => setCurrentStep('select-game')}
                            className='w-full sm:w-auto'
                        >
                            {t('shell:back_to_games')}
                        </Button>
                        <Button
                            variant='outline'
                            onClick={() => setCurrentStep('overview')}
                            className='w-full sm:w-auto'
                        >
                            {t('shell:cancel')}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    );

    const renderConfiguration = () => (
        <div className='space-y-6'>
            <Card>
                <CardHeader>
                    <CardTitle className='text-xl font-extrabold tracking-tight'>{t('shell:configure_title', { name: selectedEgg?.attributes.name })}</CardTitle>
                </CardHeader>
                <CardContent>
                    {eggPreview && (
                        <div className='space-y-6'>
                            {/* Software Configuration */}
                            <div className='space-y-4'>
                                <h3 className='text-lg font-semibold text-foreground'>{t('shell:software_config')}</h3>
                                <div className='grid grid-cols-1 xl:grid-cols-2 gap-4'>
                                    <div>
                                        <label className='text-sm font-medium text-muted-foreground block mb-2'>
                                            {t('shell:startup_command')}
                                        </label>
                                        <textarea
                                            value={customStartup}
                                            onChange={(e) => setCustomStartup(e.target.value)}
                                            placeholder={t('shell:enter_startup_command')}
                                            rows={3}
                                            className='w-full px-3 py-2 bg-muted/30 border border-border/20 rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-ring transition-colors font-mono resize-none'
                                        />
                                        <p className='text-xs text-muted-foreground mt-1'>
                                            {t('shell:use_variables_like')}{' '}
                                            {eggPreview.variables
                                                .map((v) => `{{${v.env_variable}}}`)
                                                .slice(0, 3)
                                                .join(', ')}
                                            {eggPreview.variables.length > 3 && t('shell:etc')}
                                        </p>
                                    </div>
                                    <div>
                                        <label className='text-sm font-medium text-muted-foreground block mb-2'>
                                            {t('shell:dock_image')}
                                        </label>
                                        {eggPreview.docker_images && Object.keys(eggPreview.docker_images).length > 1 ? (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <button className='w-full px-3 py-2 bg-muted/30 border border-border/20 rounded-lg text-sm text-foreground focus:outline-none focus:border-ring transition-colors text-left flex items-center justify-between hover:border-white/10'>
                                                        <span className='truncate'>
                                                            {selectedDockerImage || t('shell:select_image')}
                                                        </span>
                                                        <ChevronDown className='w-4 h-4 text-muted-foreground flex-shrink-0' />
                                                    </button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent className='w-full min-w-[300px]'>
                                                    <DropdownMenuRadioGroup
                                                        value={selectedDockerImage}
                                                        onValueChange={setSelectedDockerImage}
                                                    >
                                                        {Object.entries(eggPreview.docker_images).map(
                                                            ([displayName, _]) => (
                                                                <DropdownMenuRadioItem
                                                                    key={displayName}
                                                                    value={displayName}
                                                                    className='text-sm font-mono'
                                                                >
                                                                    <span>{displayName}</span>
                                                                </DropdownMenuRadioItem>
                                                            ),
                                                        )}
                                                    </DropdownMenuRadioGroup>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        ) : (
                                            <div className='w-full px-3 py-2 bg-muted/30 border border-border/20 rounded-lg text-sm text-foreground'>
                                                {(eggPreview.docker_images && Object.keys(eggPreview.docker_images)[0]) ||
                                                    t('shell:default_image')}
                                            </div>
                                        )}
                                        <p className='text-xs text-muted-foreground mt-1'>
                                            {t('shell:container_runtime_desc')}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Environment Variables */}
                            {eggPreview.variables.length > 0 && (
                                <div className='space-y-4'>
                                    <h3 className='text-lg font-semibold text-foreground'>{t('shell:env_variables')}</h3>
                                    <div className='grid grid-cols-1 lg:grid-cols-2 gap-4'>
                                        {eggPreview.variables.map((variable) => (
                                            <div key={variable.env_variable} className='space-y-3'>
                                                <div>
                                                    <label className='text-sm font-medium text-foreground block mb-1'>
                                                        {variable.name}
                                                        {!variable.user_editable && (
                                                            <span className='ml-2 px-2 py-0.5 text-xs bg-amber-500/20 text-amber-400 rounded'>
                                                                {t('shell:read_only')}
                                                            </span>
                                                        )}
                                                        {variable.user_editable && variable.rules.includes('required') && (
                                                            <span className='ml-2 px-2 py-0.5 text-xs bg-red-500/20 text-red-400 rounded'>
                                                                {t('shell:required')}
                                                            </span>
                                                        )}
                                                        {variable.user_editable && !variable.rules.includes('required') && (
                                                            <span className='ml-2 px-2 py-0.5 text-xs bg-neutral-500/20 text-muted-foreground rounded'>
                                                                {t('shell:optional')}
                                                            </span>
                                                        )}
                                                    </label>
                                                    {variable.description && (
                                                        <p className='text-xs text-muted-foreground mb-2'>
                                                            {variable.description}
                                                        </p>
                                                    )}
                                                </div>

                                                {variable.user_editable ? (
                                                    <div>
                                                        <input
                                                            type='text'
                                                            value={pendingVariables[variable.env_variable] || ''}
                                                            onChange={(e) =>
                                                                handleVariableChange(variable.env_variable, e.target.value)
                                                            }
                                                            placeholder={variable.default_value || t('shell:enter_value')}
                                                            className={`w-full px-3 py-2 bg-muted/30 border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none transition-colors ${variableErrors[variable.env_variable]
                                                                ? 'border-red-500 focus:border-red-500'
                                                                : 'border-border/20 focus:border-ring'
                                                                }`}
                                                        />
                                                        {variableErrors[variable.env_variable] && (
                                                            <p className='text-xs text-red-400 mt-1'>
                                                                {variableErrors[variable.env_variable]}
                                                            </p>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <div className='w-full px-3 py-2 bg-muted/10 border border-muted/30 rounded-lg text-sm text-muted-foreground font-mono'>
                                                        {pendingVariables[variable.env_variable] ||
                                                            variable.default_value ||
                                                            t('shell:not_set')}
                                                    </div>
                                                )}

                                                <div className='flex justify-between text-xs'>
                                                    <span className='text-muted-foreground font-mono'>
                                                        {variable.env_variable}
                                                    </span>
                                                    {variable.rules && (
                                                        <span className='text-muted-foreground'>{t('shell:rules', { rules: variable.rules })}</span>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Safety Options */}
                            <div className='space-y-4'>
                                <h3 className='text-lg font-semibold text-foreground'>{t('shell:safety_options')}</h3>
                                <div className='space-y-3'>
                                    <div className='flex items-center justify-between p-4 bg-muted/30 border border-border/20 rounded-lg hover:border-white/10 transition-colors'>
                                        <div className='flex-1 min-w-0 pr-4'>
                                            <label className='text-sm font-medium text-foreground block mb-1'>
                                                {t('shell:create_backup')}
                                            </label>
                                            <p className='text-xs text-muted-foreground leading-relaxed'>
                                                {backupLimit !== 0 &&
                                                    (backupLimit === null || (backups?.backupCount || 0) < backupLimit)
                                                    ? t('shell:auto_backup_desc')
                                                    : backupLimit === 0
                                                        ? t('shell:backups_disabled')
                                                        : t('shell:backup_limit_reached')}
                                            </p>
                                        </div>
                                        <div className='flex-shrink-0'>
                                            <Switch
                                                checked={shouldBackup}
                                                onCheckedChange={setShouldBackup}
                                                disabled={
                                                    backupLimit === 0 ||
                                                    (backupLimit !== null && (backups?.backupCount || 0) >= backupLimit)
                                                }
                                            />
                                        </div>
                                    </div>

                                    <div className='flex items-center justify-between p-4 bg-muted/30 border border-border/20 rounded-lg hover:border-white/10 transition-colors'>
                                        <div className='flex-1 min-w-0 pr-4'>
                                            <label className='text-sm font-medium text-foreground block mb-1'>
                                                {t('shell:wipe_files')}
                                            </label>
                                            <p className='text-xs text-muted-foreground leading-relaxed'>
                                                {t('shell:wipe_files_desc')}
                                            </p>
                                        </div>
                                        <div className='flex-shrink-0'>
                                            <Switch checked={shouldWipe} onCheckedChange={setShouldWipe} />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className='flex flex-col sm:flex-row justify-center gap-3 pt-4'>
                        <Button
                            variant='outline'
                            onClick={() => setCurrentStep('select-software')}
                            className='w-full sm:w-auto'
                        >
                            {t('shell:back_to_software')}
                        </Button>
                        <Button
                            variant='default'
                            onClick={proceedToReview}
                            disabled={!eggPreview || isLoading}
                            className='w-full sm:w-auto'
                        >
                            {isLoading && <Spinner size='small' />}
                            {t('shell:review_changes')}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );

    const renderReview = () => (
        <div className='space-y-6'>
            <Card>
                <CardHeader>
                    <CardTitle className='text-xl font-extrabold tracking-tight'>{t('shell:review_changes')}</CardTitle>
                </CardHeader>
                <CardContent>
                    {selectedEgg && eggPreview && (
                        <div className='space-y-6'>
                            {/* Summary */}
                            <div className='p-4 bg-muted/30 border border-border/20 rounded-lg'>
                                <h3 className='text-lg font-semibold text-foreground mb-4'>{t('shell:change_summary')}</h3>
                                <div className='grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm'>
                                    <div>
                                        <span className='text-muted-foreground'>{t('shell:from')}</span>
                                        <div className='text-foreground font-medium'>
                                            {currentEggName || t('shell:no_software')}
                                        </div>
                                    </div>
                                    <div>
                                        <span className='text-muted-foreground'>{t('shell:to')}</span>
                                        <div className='text-primary font-medium'>{selectedEgg.attributes.name}</div>
                                    </div>
                                    <div>
                                        <span className='text-muted-foreground'>{t('shell:category')}</span>
                                        <div className='text-foreground font-medium'>{selectedNest?.attributes.name}</div>
                                    </div>
                                    <div>
                                        <span className='text-muted-foreground'>{t('shell:dock_image_review')}</span>
                                        <div className='text-foreground font-medium'>
                                            {selectedDockerImage || t('shell:default')}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Startup Command Review */}
                            <div className='p-4 bg-muted/30 border border-border/20 rounded-lg'>
                                <h3 className='text-lg font-semibold text-foreground mb-4'>{t('shell:startup_configuration')}</h3>
                                <div className='space-y-3'>
                                    <div>
                                        <span className='text-muted-foreground text-sm'>{t('shell:startup_command_review')}</span>
                                        <div className='mt-1 p-3 bg-muted/30 border border-border/20 rounded-lg font-mono text-sm text-foreground whitespace-pre-wrap'>
                                            {customStartup || eggPreview.egg.startup}
                                        </div>
                                    </div>
                                    <div>
                                        <span className='text-muted-foreground text-sm'>{t('shell:dock_image_review')}</span>
                                        <div className='mt-1 p-3 bg-muted/30 border border-border/20 rounded-lg text-sm text-foreground'>
                                            {selectedDockerImage || t('shell:default_image')}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Configuration Review */}
                            {eggPreview.variables.length > 0 && (
                                <div className='p-4 bg-muted/30 border border-border/20 rounded-lg'>
                                    <h3 className='text-lg font-semibold text-foreground mb-4'>{t('shell:variable_configuration')}</h3>
                                    <div className='space-y-2'>
                                        {eggPreview.variables.map((variable) => (
                                            <div
                                                key={variable.env_variable}
                                                className='flex justify-between items-center py-2 px-3 bg-muted/30 rounded-lg'
                                            >
                                                <div>
                                                    <span className='text-foreground font-medium'>{variable.name}</span>
                                                    <span className='text-muted-foreground text-sm ml-2 font-mono'>
                                                        ({variable.env_variable})
                                                    </span>
                                                </div>
                                                <div className='text-primary font-mono text-sm'>
                                                    {pendingVariables[variable.env_variable] ||
                                                        variable.default_value ||
                                                        t('shell:not_set')}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Safety Options Review */}
                            <div className='p-4 bg-muted/30 border border-border/20 rounded-lg'>
                                <h3 className='text-lg font-semibold text-foreground mb-4'>{t('shell:safety_options')}</h3>
                                <div className='space-y-2'>
                                    <div className='flex justify-between items-center py-2 px-3 bg-muted/30 rounded-lg'>
                                        <span className='text-foreground'>{t('shell:create_backup')}</span>
                                        <span className={shouldBackup ? 'text-green-400' : 'text-muted-foreground'}>
                                            {shouldBackup ? t('shell:yes') : t('shell:no')}
                                        </span>
                                    </div>
                                    <div className='flex justify-between items-center py-2 px-3 bg-muted/30 rounded-lg'>
                                        <span className='text-foreground'>{t('shell:wipe_files')}</span>
                                        <span className={shouldWipe ? 'text-amber-400' : 'text-muted-foreground'}>
                                            {shouldWipe ? t('shell:yes') : t('shell:no')}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Subdomain Warnings */}
                            {eggPreview.warnings && eggPreview.warnings.length > 0 && (
                                <div className='space-y-3'>
                                    {eggPreview.warnings.map((warning, index) => (
                                        <div
                                            key={index}
                                            className={`p-4 border rounded-lg ${warning.severity === 'error'
                                                ? 'bg-red-500/10 border-red-500/20'
                                                : 'bg-amber-500/10 border-amber-500/20'
                                                }`}
                                        >
                                            <div className='flex items-start gap-3'>
                                                <TriangleAlert
                                                    className={`w-5 h-5 flex-shrink-0 mt-0.5 ${warning.severity === 'error' ? 'text-red-400' : 'text-amber-400'
                                                        }`}
                                                />
                                                <div>
                                                    <h4
                                                        className={`font-semibold mb-2 ${warning.severity === 'error' ? 'text-red-400' : 'text-amber-400'
                                                            }`}
                                                    >
                                                        {warning.type === 'subdomain_incompatible'
                                                            ? t('shell:subdomain_deleted')
                                                            : t('shell:warning')}
                                                    </h4>
                                                    <p className='text-sm text-muted-foreground'>{warning.message}</p>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* General Warning */}
                            <div className='p-4 bg-amber-500/10 border border-amber-500/20 rounded-lg'>
                                <div className='flex items-start gap-3'>
                                    <TriangleAlert
                                        className='w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5'
                                    />
                                    <div>
                                        <h4 className='text-amber-400 font-semibold mb-2'>{t('shell:this_will')}</h4>
                                        <ul className='text-sm text-muted-foreground'>
                                            <li>{t('shell:stop_reinstall')}</li>
                                            <li>{t('shell:take_several_minutes')}</li>
                                            <li>{t('shell:modify_remove_files')}</li>
                                        </ul>
                                        <span className='text-sm font-bold mt-4'>
                                            {t('shell:backup_advice')}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className='flex flex-col sm:flex-row justify-center gap-3 pt-4'>
                        <Button
                            variant='outline'
                            onClick={() => setCurrentStep('configure')}
                            className='w-full sm:w-auto'
                        >
                            {t('shell:back_to_configure')}
                        </Button>
                        <Button
                            variant='default'
                            onClick={applyChanges}
                            disabled={isLoading}
                            className='w-full sm:w-auto'
                        >
                            {isLoading && <Spinner size='small' />}
                            {t('shell:apply_changes')}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );

    // Show loading state if server data is not available
    if (!serverData) {
        return (
            <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
                <FlashMessageRender byKey={'shell'} />
                <div className='flex items-center justify-center h-64'>
                    <div className='flex flex-col items-center text-center'>
                        <Spinner size='large' />
                        <p className='text-muted-foreground mt-4'>{t('shell:loading_server_info')}</p>
                    </div>
                </div>
            </div>
        );
    }
    function RenderOperationModal() {
        if (daemonType == 'elytra') {
            return (
                <OperationProgressModal
                    visible={showOperationModal}
                    operationId={currentOperationId}
                    operationType={t('shell:operation_type')}
                    onClose={closeOperationModal}
                    onComplete={handleOperationComplete}
                    onError={handleOperationError}
                />
            );
        }
        if (daemonType == 'wings') {
            return (
                <WingsOperationProgressModal
                    visible={showOperationModal}
                    operationId={currentOperationId}
                    operationType={t('shell:operation_type')}
                    onClose={closeOperationModal}
                    onComplete={handleOperationComplete}
                    onError={handleOperationError}
                />
            );
        }
        return <div>{t('shell:no_daemon_modal', { daemonType })}</div>;
    }
    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'server:software'} />
            <div className='space-y-6'>
                <MainPageHeader direction='column' title={t('shell:software_management')}>
                    <p className='text-muted-foreground leading-relaxed'>
                        {t('shell:software_management_desc')}
                    </p>
                </MainPageHeader>

                {currentStep !== 'overview' && (
                    <div className='rounded-lg border border-border/20 bg-muted/30 p-4'>
                        <div className='mb-2 flex items-center justify-between'>
                            <span className='text-sm font-medium capitalize text-foreground'>
                                {t(`shell:step_${currentStep.replace('-', '_')}`)}
                            </span>
                            <span className='text-sm text-muted-foreground'>
                                {t('shell:step_of', {
                                    current: ['overview', 'select-game', 'select-software', 'configure', 'review'].indexOf(currentStep),
                                    total: 4,
                                })}
                            </span>
                        </div>
                        <div className='h-2 w-full rounded-full bg-muted/20'>
                            <div
                                className='h-2 rounded-full bg-primary transition-all duration-300'
                                style={{
                                    width: `${(['overview', 'select-game', 'select-software', 'configure', 'review'].indexOf(currentStep) / 4) * 100}%`,
                                }}
                            />
                        </div>
                    </div>
                )}

                {currentStep === 'overview' && renderOverview()}
                {currentStep === 'select-game' && renderGameSelection()}
                {currentStep === 'select-software' && renderSoftwareSelection()}
                {currentStep === 'configure' && renderConfiguration()}
                {currentStep === 'review' && renderReview()}
            </div>

            <Dialog open={showWipeConfirmation} onOpenChange={setShowWipeConfirmation}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('shell:wipe_title')}</DialogTitle>
                    </DialogHeader>
                    <div className='space-y-4'>
                        <div className='flex items-start gap-3 rounded-lg border border-red-500/20 bg-red-500/10 p-4'>
                            <TriangleAlert className='mt-0.5 size-5 shrink-0 text-red-400' />
                            <div>
                                <h4 className='mb-2 font-semibold text-red-400'>{t('shell:danger_no_backup')}</h4>
                                <Trans i18nKey='shell:wipe_no_backup_desc'>
                                    <p className='text-sm text-muted-foreground'>
                                        You have chosen to wipe all files <strong>without creating a backup</strong>. This
                                        action will <strong>permanently delete ALL files</strong> on your server and cannot
                                        be undone.
                                    </p>
                                </Trans>
                            </div>
                        </div>
                        <div className='space-y-2 text-sm text-muted-foreground'>
                            <p><strong>{t('shell:what_will_happen')}</strong></p>
                            <ul className='ml-4 list-inside list-disc space-y-1'>
                                <li>{t('shell:wipe_bullet_1')}</li>
                                <li>{t('shell:wipe_bullet_2')}</li>
                                <li>{t('shell:wipe_bullet_3')}</li>
                                <li>{t('shell:wipe_bullet_4')}</li>
                            </ul>
                        </div>
                        <p className='text-sm text-muted-foreground'>
                            {t('shell:wipe_confirm_question')}
                        </p>
                    </div>
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setShowWipeConfirmation(false)}>{t('shell:cancel')}</Button>
                        <Button
                            variant='destructive'
                            onClick={handleWipeConfirm}
                            disabled={wipeCountdown > 0 && !shiftPressed}
                        >
                            {wipeCountdown > 0 ? t('shell:yes_wipe_files_countdown', { countdown: wipeCountdown }) : t('shell:yes_wipe_files')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {RenderOperationModal()}
        </div>
    );
};

export default SoftwareContainer;
