<template>
    <AppLayout>
        <div class="container mx-auto py-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <Heading :title="`Update Custody - ${employee.full_name}`" />
                            <div class="flex items-center gap-2">
                                <Badge :class="getStatusBadgeClass(employee.employment_status)">
                                    {{ t(`employees.status_${employee.employment_status}`) }}
                                </Badge>
                                <span class="text-sm font-mono text-muted-foreground">{{ employee.employee_id }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" asChild>
                                <Link :href="route('employees.show', employee.id)">
                                    <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                                    Back to Employee
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Current Custody Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Icon name="Package" class="h-5 w-5 text-blue-600" />
                                Current Custody ({{ currentAssets.length }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div 
                                    v-for="asset in currentAssets" 
                                    :key="asset.id"
                                    class="flex items-center justify-between p-3 border rounded-lg"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <Icon name="Package" class="h-4 w-4 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ asset.model_name || asset.asset_tag }}</p>
                                            <p class="text-sm text-muted-foreground">{{ asset.asset_tag }}</p>
                                            <p class="text-xs text-muted-foreground">{{ asset.assetCategory?.name }}</p>
                                        </div>
                                    </div>
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        @click="removeAsset(asset)"
                                        :disabled="loading"
                                    >
                                        <Icon name="Minus" class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div v-if="currentAssets.length === 0" class="text-center py-8 text-muted-foreground">
                                    No assets currently assigned
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Updated Custody Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Icon name="Package" class="h-5 w-5 text-green-600" />
                                Updated Custody ({{ updatedAssets.length }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div 
                                    v-for="asset in updatedAssets" 
                                    :key="asset.id"
                                    class="flex items-center justify-between p-3 border rounded-lg"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                            <Icon name="Package" class="h-4 w-4 text-green-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ asset.model_name || asset.asset_tag }}</p>
                                            <p class="text-sm text-muted-foreground">{{ asset.asset_tag }}</p>
                                            <p class="text-xs text-muted-foreground">{{ asset.assetCategory?.name }}</p>
                                        </div>
                                    </div>
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        @click="removeFromUpdated(asset)"
                                        :disabled="loading"
                                    >
                                        <Icon name="X" class="h-4 w-4" />
                                    </Button>
                                </div>
                                
                                <!-- Add Asset Button -->
                                <Button 
                                    variant="dashed" 
                                    class="w-full"
                                    @click="showAssetSearch = true"
                                    :disabled="loading"
                                >
                                    <Icon name="Plus" class="mr-2 h-4 w-4" />
                                    Add Asset
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                
                <!-- Changes Summary -->
                <Card v-if="hasChanges">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Icon name="FileText" class="h-5 w-5 text-orange-600" />
                            Changes Summary
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <Label for="changes_summary" class="mb-2">Summary (Optional)</Label>
                                <textarea 
                                    id="changes_summary"
                                    v-model="changesSummary"
                                    rows="3"
                                    placeholder="Describe the reason for this custody change..."
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                ></textarea>
                            </div>
                            
                            <div>
                                <Label for="document" class="mb-2">Upload Document (Proof of Action)</Label>
                                <Input 
                                    id="document"
                                    type="file" 
                                    accept=".pdf,.jpg,.jpeg,.png,.gif"
                                    @change="handleDocumentUpload" 
                                />
                                <p class="text-xs text-muted-foreground mt-1">
                                    PDF, JPG, PNG, or GIF files up to 10MB
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Action Buttons -->
                <div class="flex justify-end gap-4" v-if="hasChanges">
                    <Button variant="outline" @click="resetChanges" :disabled="loading">
                        Reset Changes
                    </Button>
                    <Button @click="saveCustodyUpdate" :disabled="loading">
                        <Icon name="Save" class="mr-2 h-4 w-4" />
                        {{ loading ? 'Saving...' : 'Save Custody Update' }}
                    </Button>
                </div>
                
                <!-- Recent Custody Changes -->
                <Card v-if="recentCustodyChanges.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Icon name="History" class="h-5 w-5 text-gray-600" />
                            Recent Custody Changes
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div 
                                v-for="change in recentCustodyChanges" 
                                :key="change.id"
                                class="flex items-center justify-between p-3 border rounded-lg"
                            >
                                <div>
                                    <p class="font-medium">{{ change.changes_summary || 'Custody Updated' }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ new Date(change.created_at).toLocaleDateString() }} by {{ change.updatedBy?.name }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge :class="change.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                        {{ change.status }}
                                    </Badge>
                                    <Button variant="outline" size="sm" @click="viewCustodyDocument(change)">
                                        <Icon name="FileText" class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
        
        <!-- Asset Search Dialog -->
        <Dialog v-model:open="showAssetSearch">
            <DialogContent class="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>Select Assets to Add</DialogTitle>
                </DialogHeader>
                
                <div class="space-y-4">
                    <div>
                        <Input 
                            v-model="assetSearchQuery"
                            placeholder="Search assets by tag, model, or serial number..."
                            @input="searchAssets"
                        />
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto">
                        <div class="space-y-2">
                            <div 
                                v-for="asset in searchResults" 
                                :key="asset.id"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer"
                                @click="toggleAssetSelection(asset)"
                            >
                                <div class="flex items-center gap-3">
                                    <Checkbox 
                                        :checked="selectedAssetIds.has(asset.id)"
                                        @update:checked="toggleAssetSelection(asset)"
                                    />
                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                        <Icon name="Package" class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ asset.model_name || asset.asset_tag }}</p>
                                        <p class="text-sm text-muted-foreground">{{ asset.asset_tag }}</p>
                                        <p class="text-xs text-muted-foreground">{{ asset.assetCategory?.name }} - {{ asset.location?.name }}</p>
                                    </div>
                                </div>
                                <Badge variant="outline">{{ asset.status }}</Badge>
                            </div>
                            
                            <div v-if="searchResults.length === 0 && assetSearchQuery" class="text-center py-8 text-muted-foreground">
                                No available assets found
                            </div>
                        </div>
                    </div>
                </div>
                
                <DialogFooter>
                    <Button variant="outline" @click="showAssetSearch = false">Cancel</Button>
                    <Button @click="addSelectedAssets" :disabled="selectedAssetIds.size === 0">
                        Add {{ selectedAssetIds.size }} Asset(s)
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref, onMounted } from 'vue';
import type { Employee, Asset, CustodyChange, BreadcrumbItem } from '@/types';

const { t } = useI18n();

interface Props {
    employee: Employee;
    currentAssets: Asset[];
    availableAssets: Asset[];
    recentCustodyChanges: CustodyChange[];
}

const props = defineProps<Props>();

// Reactive state
const loading = ref(false);
const updatedAssets = ref<Asset[]>([...props.currentAssets]);
const changesSummary = ref('');
const selectedDocument = ref<File | null>(null);
const showAssetSearch = ref(false);
const assetSearchQuery = ref('');
const searchResults = ref<Asset[]>([...props.availableAssets]);
const selectedAssetIds = ref<Set<number>>(new Set());

// Computed properties
const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('employees.title'),
        href: '/employees',
    },
    {
        title: props.employee.full_name,
        href: `/employees/${props.employee.id}`,
    },
    {
        title: 'Update Custody',
        href: `/employees/${props.employee.id}/custody`,
    },
]);

const hasChanges = computed(() => {
    const currentIds = new Set(props.currentAssets.map(a => a.id));
    const updatedIds = new Set(updatedAssets.value.map(a => a.id));
    
    if (currentIds.size !== updatedIds.size) return true;
    
    for (const id of currentIds) {
        if (!updatedIds.has(id)) return true;
    }
    
    return false;
});

// Methods
const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-yellow-100 text-yellow-800';
        case 'terminated':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const removeAsset = (asset: Asset) => {
    updatedAssets.value = updatedAssets.value.filter(a => a.id !== asset.id);
};

const removeFromUpdated = (asset: Asset) => {
    updatedAssets.value = updatedAssets.value.filter(a => a.id !== asset.id);
};

const resetChanges = () => {
    updatedAssets.value = [...props.currentAssets];
    changesSummary.value = '';
    selectedDocument.value = null;
};

const handleDocumentUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedDocument.value = target.files[0];
    }
};

const searchAssets = async () => {
    if (assetSearchQuery.value.length < 2) {
        searchResults.value = props.availableAssets;
        return;
    }
    
    try {
        const response = await fetch(`/api/custody/available-assets?search=${encodeURIComponent(assetSearchQuery.value)}`);
        const assets = await response.json();
        searchResults.value = assets;
    } catch (error) {
        console.error('Asset search failed:', error);
        searchResults.value = [];
    }
};

const toggleAssetSelection = (asset: Asset) => {
    if (selectedAssetIds.value.has(asset.id)) {
        selectedAssetIds.value.delete(asset.id);
    } else {
        selectedAssetIds.value.add(asset.id);
    }
};

const addSelectedAssets = () => {
    const assetsToAdd = searchResults.value.filter(asset => selectedAssetIds.value.has(asset.id));
    
    // Add assets that aren't already in the updated list
    assetsToAdd.forEach(asset => {
        if (!updatedAssets.value.find(a => a.id === asset.id)) {
            updatedAssets.value.push(asset);
        }
    });
    
    // Clear selection and close dialog
    selectedAssetIds.value.clear();
    showAssetSearch.value = false;
    assetSearchQuery.value = '';
    searchResults.value = props.availableAssets;
};

const saveCustodyUpdate = async () => {
    if (!hasChanges.value) {
        alert('No changes to save');
        return;
    }
    
    loading.value = true;
    
    try {
        const formData = new FormData();
        formData.append('new_asset_ids', JSON.stringify(updatedAssets.value.map(a => a.id)));
        
        if (changesSummary.value) {
            formData.append('changes_summary', changesSummary.value);
        }
        
        if (selectedDocument.value) {
            formData.append('document', selectedDocument.value);
        }
        
        const response = await fetch(route('employees.custody.store', props.employee.id), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        if (response.ok) {
            const result = await response.json();
            alert('Custody updated successfully!');
            
            // Redirect back to employee page
            router.visit(route('employees.show', props.employee.id));
        } else {
            const error = await response.json();
            alert('Failed to update custody: ' + (error.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating custody:', error);
        alert('Failed to update custody. Please try again.');
    } finally {
        loading.value = false;
    }
};

const viewCustodyDocument = (custodyChange: CustodyChange) => {
    window.open(route('custody.document', custodyChange.id), '_blank');
};

// Initialize search results
onMounted(() => {
    searchResults.value = props.availableAssets;
});
</script> 