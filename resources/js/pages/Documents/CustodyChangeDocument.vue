<template>
    <div>
        <Head title="Custody Change Document" />

        <div class="container mx-auto py-8 print:py-0">
            <!-- Print Controls -->
            <div class="flex justify-between items-center mb-6 print:hidden">
                <div>
                    <h1 class="text-2xl font-bold">Custody Change Document</h1>
                    <p class="text-muted-foreground">Document ID: {{ custodyChange.id }}</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="goBack">
                        <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                    <Button @click="printDocument">
                        <Icon name="Printer" class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                </div>
            </div>

            <!-- Document Content -->
            <div class="bg-white shadow-lg print:shadow-none">
                <div class="p-8 print:p-4">
                    <!-- Company Info -->
                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold">{{ employee.company?.name_en || 'Company' }}</h2>
                        <p class="text-sm text-muted-foreground">{{ employee.company?.company_email || '' }}</p>
                    </div>

                    <!-- Header -->
                    <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
                        <h1 class="text-2xl font-bold mb-2">Asset Custody Change Form</h1>
                        <p><strong>Document ID:</strong> {{ custodyChange.id }}</p>
                        <p><strong>Date:</strong> {{ new Date(custodyChange.created_at).toLocaleString() }}</p>
                        <Badge :class="getStatusBadgeClass(custodyChange.status)" class="mt-2">
                            {{ custodyChange.status.toUpperCase() }}
                        </Badge>
                    </div>
                    
                    <!-- Employee Info -->
                    <div class="grid grid-cols-2 gap-8 mb-6">
                        <div>
                            <div class="mb-2">
                                <span class="font-semibold text-gray-600">Employee Name:</span> {{ employee.full_name }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold text-gray-600">Employee ID:</span> {{ employee.employee_id }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold text-gray-600">Email:</span> {{ employee.email }}
                            </div>
                        </div>
                        <div>
                            <div class="mb-2">
                                <span class="font-semibold text-gray-600">Department:</span> {{ employee.department || 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold text-gray-600">Job Title:</span> {{ employee.job_title || 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <span class="font-semibold text-gray-600">Updated By:</span> {{ custodyChange.updatedBy?.name || 'System' }}
                            </div>
                        </div>
                    </div>

                    <!-- Changes Summary -->
                    <div v-if="custodyChange.changes_summary" class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <strong>Change Summary:</strong> {{ custodyChange.changes_summary }}
                    </div>
                    
                    <!-- Previous Custody -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold mb-4">Previous Custody ({{ previousAssets.length }} items)</h2>
                        <div v-if="previousAssets.length > 0" class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-300 px-3 py-2 text-left">Asset Tag</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Description</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Model</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Serial Number</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Category</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Location</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Condition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="asset in previousAssets" :key="asset.id">
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.asset_tag }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.model_name || asset.asset_tag }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.model_number || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.serial_number || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.category_name || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.location_name || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.condition ? asset.condition.charAt(0).toUpperCase() + asset.condition.slice(1) : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 italic">
                            No assets were previously assigned
                        </div>
                    </div>
                    
                    <!-- New Custody -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold mb-4">New Custody ({{ newAssets.length }} items)</h2>
                        <div v-if="newAssets.length > 0" class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-300 px-3 py-2 text-left">Asset Tag</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Description</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Model</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Serial Number</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Category</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Location</th>
                                        <th class="border border-gray-300 px-3 py-2 text-left">Condition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="asset in newAssets" :key="asset.id">
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.asset_tag }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.model_name || asset.asset_tag }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.model_number || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.serial_number || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.category_name || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.location_name || 'N/A' }}</td>
                                        <td class="border border-gray-300 px-3 py-2">{{ asset.condition ? asset.condition.charAt(0).toUpperCase() + asset.condition.slice(1) : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 italic">
                            No assets assigned in new custody
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">Terms and Conditions</h3>
                        <ul class="text-sm space-y-2 pl-6">
                            <li class="list-disc">I acknowledge receipt of the assets listed above and accept responsibility for their care and proper use.</li>
                            <li class="list-disc">I understand that I am accountable for any loss, damage, or theft of company assets in my custody.</li>
                            <li class="list-disc">I agree to return all assets in good condition upon request or when my employment ends.</li>
                            <li class="list-disc">I will immediately report any loss, damage, or theft of company assets to my supervisor.</li>
                            <li class="list-disc">I understand that failure to return company assets may result in deductions from my final pay.</li>
                        </ul>
                    </div>
                    
                    <!-- Signatures -->
                    <div class="print:mt-12">
                        <h3 class="text-lg font-bold mb-6">Signatures</h3>
                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <div class="border border-gray-800 h-20 relative mb-4">
                                    <div class="absolute bottom-1 left-2 text-xs text-gray-600">Employee Signature</div>
                                </div>
                                <div class="text-center">
                                    <strong>{{ employee.full_name }}</strong><br>
                                    <small>{{ employee.job_title || 'Employee' }}</small><br>
                                    <small>Date: _______________________</small>
                                </div>
                            </div>
                            <div>
                                <div class="border border-gray-800 h-20 relative mb-4">
                                    <div class="absolute bottom-1 left-2 text-xs text-gray-600">Admin/Manager Signature</div>
                                </div>
                                <div class="text-center">
                                    <strong>{{ custodyChange.updatedBy?.name || '____________________' }}</strong><br>
                                    <small>IT Administrator</small><br>
                                    <small>Date: _______________________</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 text-center text-xs text-gray-500 print:mt-12">
                        <p>This document was generated automatically on {{ generatedAt }}</p>
                        <p>Document ID: {{ custodyChange.id }} | Status: {{ custodyChange.status }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import Icon from '@/components/Icon.vue';
import type { Employee, CustodyChange } from '@/types';

interface Props {
    custodyChange: CustodyChange;
    employee: Employee;
    previousAssets: any[];
    newAssets: any[];
    generatedAt: string;
}

defineProps<Props>();

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-blue-100 text-blue-800';
        case 'signed':
            return 'bg-purple-100 text-purple-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const printDocument = () => {
    window.print();
};

const goBack = () => {
    window.history.back();
};
</script>

<style>
@media print {
    @page {
        margin: 0.5in;
    }
    
    body {
        -webkit-print-color-adjust: exact;
    }
}
</style> 