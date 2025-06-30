<template>
  <Head :title="asset.asset_tag" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Heading :title="asset.asset_tag" />
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ t('assets.asset_details') }}
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <Button
            variant="outline"
            @click="showBarcodeDialog"
            class="inline-flex items-center"
          >
            <Icon name="Printer" class="mr-2 h-4 w-4" />
            Barcode
          </Button>
          <Link 
            :href="route('assets.edit', asset.id)"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
          >
            <Icon name="Edit" class="mr-2 h-4 w-4" />
            {{ t('common.edit') }}
          </Link>
          <Button
            variant="destructive"
            @click="confirmDelete"
          >
            <Icon name="Trash2" class="mr-2 h-4 w-4" />
            {{ t('common.delete') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Asset Image -->
          <Card v-if="asset.image_path">
            <CardHeader>
              <CardTitle>{{ t('assets.asset_image') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex justify-center">
                <img
                  :src="`/storage/${asset.image_path}`"
                  :alt="asset.asset_tag"
                  class="max-w-full h-auto max-h-96 rounded-lg border border-gray-200 dark:border-gray-700"
                />
              </div>
            </CardContent>
          </Card>

          <!-- Basic Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('assets.basic_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.asset_tag') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                    {{ asset.asset_tag }}
                  </p>
                </div>
                
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.serial_number') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                    {{ asset.serial_number || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.manufacturer') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.manufacturer || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.model_name') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.model_name || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.model_number') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.model_number || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.status') }}
                  </Label>
                  <Badge 
                    :variant="getStatusVariant(asset.status)"
                    class="mt-1"
                  >
                    {{ t(`assets.status_${asset.status}`) }}
                  </Badge>
                </div>
              </div>

              <div v-if="asset.notes">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.notes') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                  {{ asset.notes }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Template Information -->
          <Card v-if="asset.asset_template">
            <CardHeader>
              <CardTitle>{{ t('assets.template_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.template_name') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.asset_template.name }}
                  </p>
                </div>
                
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.template_type') }}
                  </Label>
                  <Badge 
                    :variant="asset.asset_template.is_global ? 'secondary' : 'default'"
                    class="mt-1"
                  >
                    {{ asset.asset_template.is_global ? t('asset_templates.global') : asset.asset_template.company?.name_en }}
                  </Badge>
                </div>
              </div>
              
              <div class="flex justify-end">
                <Link 
                  :href="route('asset-templates.show', asset.asset_template.id)"
                  class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('assets.view_template') }}
                  <Icon name="ExternalLink" class="ml-1 h-3 w-3" />
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Location Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('assets.location_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.location') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.location?.name || '—' }}
                </p>
              </div>
              
              <div v-if="asset.location?.code">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('locations.code') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                  {{ asset.location.code }}
                </p>
              </div>

              <div v-if="asset.location?.building">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('locations.building') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.location.building }}
                </p>
              </div>

              <div v-if="asset.location?.office_number">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('locations.office_number') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.location.office_number }}
                </p>
              </div>

              <div v-if="asset.location" class="flex justify-end">
                <Link 
                  :href="route('locations.show', asset.location.id)"
                  class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('assets.view_location') }}
                  <Icon name="ExternalLink" class="ml-1 h-3 w-3" />
                </Link>
              </div>
            </CardContent>
          </Card>

          <!-- Category Information -->
          <Card v-if="asset.category">
            <CardHeader>
              <CardTitle>{{ t('assets.category_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.category') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.category.name }}
                </p>
              </div>
              
              <div v-if="asset.category.code">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('asset_categories.code') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                  {{ asset.category.code }}
                </p>
              </div>

              <div class="flex justify-end">
                <Link 
                  :href="route('asset-categories.show', asset.category.id)"
                  class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('assets.view_category') }}
                  <Icon name="ExternalLink" class="ml-1 h-3 w-3" />
                </Link>
              </div>
            </CardContent>
          </Card>

          <!-- Asset Metadata -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('assets.asset_metadata') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.created_at') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(asset.created_at).toLocaleDateString() }}
                </p>
              </div>
              
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.updated_at') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(asset.updated_at).toLocaleDateString() }}
                </p>
              </div>

              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.company') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.company?.name_en || '—' }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Assignment Information -->
          <Card v-if="asset.status === 'assigned' && asset.assigned_to">
            <CardHeader>
              <CardTitle>{{ t('assets.assignment_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.assigned_to') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.assigned_to?.name || '—' }}
                </p>
              </div>
              
              <div v-if="asset.assigned_date">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.assigned_date') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(asset.assigned_date).toLocaleDateString() }}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Barcode Print Dialog -->
    <Dialog v-model:open="barcodeDialog.show">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Print Barcode Label</DialogTitle>
          <DialogDescription>
            Configure printer settings for the Zebra label printer.
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <div class="flex items-center space-x-2">
            <input 
              type="checkbox" 
              id="useDefaultPrinter" 
              v-model="barcodeDialog.useDefaultPrinter"
              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            />
            <Label for="useDefaultPrinter" class="font-medium">
              Print to Default printer
            </Label>
          </div>
          
          <div v-if="!barcodeDialog.useDefaultPrinter" class="space-y-2">
            <Label for="installedPrinterName">Select an installed Printer:</Label>
            <select 
              id="installedPrinterName" 
              v-model="barcodeDialog.selectedPrinter"
              class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="">Select a printer...</option>
              <option v-for="printer in barcodeDialog.availablePrinters" :key="printer" :value="printer">
                {{ printer }}
              </option>
            </select>
          </div>

          <div v-if="barcodeDialog.status" class="text-sm text-gray-600 dark:text-gray-400">
            Status: {{ barcodeDialog.status }}
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="barcodeDialog.show = false">
            Cancel
          </Button>
          <Button 
            @click="printBarcode" 
            :disabled="barcodeDialog.printing || (!barcodeDialog.useDefaultPrinter && !barcodeDialog.selectedPrinter)"
          >
            <Icon v-if="barcodeDialog.printing" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
            Print Now
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialog.show">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ t('assets.confirm_delete') }}</DialogTitle>
          <DialogDescription>
            {{ t('assets.delete_warning') }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="deleteDialog.show = false">
            {{ t('common.cancel') }}
          </Button>
          <Button variant="destructive" @click="handleDelete" :disabled="deleteDialog.loading">
            <Icon v-if="deleteDialog.loading" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
            {{ t('common.delete') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Icon from '@/components/Icon.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItem } from '@/types'

// Type declarations for JSPrintManager
declare global {
  interface Window {
    JSPM: {
      JSPrintManager: {
        auto_reconnect: boolean
        start: () => void
        websocket_status: number
        WS: {
          onStatusChanged: () => void
        }
        getPrinters: () => Promise<string[]>
      }
      WSStatus: {
        Open: number
        Closed: number
        Blocked: number
      }
      ClientPrintJob: new () => {
        clientPrinter: any
        printerCommands: string
        sendToClient: () => void
      }
      DefaultPrinter: new () => any
      InstalledPrinter: new (printerName: string) => any
    }
  }
}

const { t } = useI18n()

interface Asset {
  id: number
  asset_tag: string
  serial_number?: string
  manufacturer?: string
  model_name?: string
  model_number?: string
  status: string
  notes?: string
  image_path?: string
  assigned_date?: string
  created_at: string
  updated_at: string
  asset_template?: {
    id: number
    name: string
    is_global: boolean
    company?: {
      name_en: string
    }
  }
  location?: {
    id: number
    name: string
    code?: string
    building?: string
    office_number?: string
  }
  category?: {
    id: number
    name: string
    code?: string
  }
  company?: {
    name_en: string
  }
  assigned_to?: {
    name: string
  }
}

interface Props {
  asset: Asset
}

const props = defineProps<Props>()

const deleteDialog = ref({
  show: false,
  loading: false
})

const barcodeDialog = ref({
  show: false,
  printing: false,
  useDefaultPrinter: false,
  selectedPrinter: '',
  availablePrinters: [] as string[],
  status: ''
})

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: t('nav.dashboard'),
    href: '/dashboard',
  },
  {
    title: t('nav.assets'),
    href: '/assets',
  },
  {
    title: props.asset.asset_tag,
    href: `/assets/${props.asset.id}`,
  },
])

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'available':
      return 'default'
    case 'assigned':
      return 'secondary'
    case 'maintenance':
      return 'destructive'
    case 'retired':
      return 'outline'
    default:
      return 'secondary'
  }
}

const confirmDelete = () => {
  deleteDialog.value.show = true
}

const handleDelete = () => {
  deleteDialog.value.loading = true
  
  router.delete(route('assets.destroy', props.asset.id), {
    onSuccess: () => {
      router.visit(route('assets.index'))
    },
    onFinish: () => {
      deleteDialog.value.loading = false
    }
  })
}

// Function to dynamically load JavaScript files
const loadScript = (src: string): Promise<void> => {
  return new Promise((resolve, reject) => {
    // Check if script is already loaded
    const existingScript = document.querySelector(`script[src="${src}"]`)
    if (existingScript) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = src
    script.onload = () => resolve()
    script.onerror = () => reject(new Error(`Failed to load script: ${src}`))
    document.head.appendChild(script)
  })
}

// Load all required scripts
const loadRequiredScripts = async (): Promise<void> => {
  try {
    barcodeDialog.value.status = 'Loading print libraries...'
    
    // Load scripts in sequence
    await loadScript('/js/bluebird.min.js')
    await loadScript('/js/jquery-3.2.1.slim.min.js') 
    await loadScript('/js/jsmanager/JSPrintManager.js')
    
    barcodeDialog.value.status = 'Print libraries loaded successfully'
  } catch (error) {
    console.error('Failed to load scripts:', error)
    barcodeDialog.value.status = 'Failed to load print libraries'
  }
}

const showBarcodeDialog = async () => {
  barcodeDialog.value.show = true
  
  // Load scripts if not already loaded
  if (typeof window.JSPM === 'undefined') {
    await loadRequiredScripts()
  }
  
  initializeJSPrintManager()
}

const initializeJSPrintManager = () => {
  nextTick(() => {
    if (typeof window.JSPM !== 'undefined') {
      // WebSocket settings
      window.JSPM.JSPrintManager.auto_reconnect = true
      window.JSPM.JSPrintManager.start()
      window.JSPM.JSPrintManager.WS.onStatusChanged = function () {
        if (jspmWSStatus()) {
          barcodeDialog.value.status = 'Connected to JSPrintManager'
          // Get client installed printers
          window.JSPM.JSPrintManager.getPrinters().then(function (myPrinters: string[]) {
            barcodeDialog.value.availablePrinters = myPrinters
          })
        }
      }
    } else {
      barcodeDialog.value.status = 'JSPrintManager not loaded'
    }
  })
}

const jspmWSStatus = () => {
  if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Open) {
    return true
  } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Closed) {
    barcodeDialog.value.status = 'JSPrintManager is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm'
    return false
  } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Blocked) {
    barcodeDialog.value.status = 'JSPM has blocked this website!'
    return false
  }
  return false
}

const printBarcode = () => {
  if (jspmWSStatus()) {
    barcodeDialog.value.printing = true
    
    try {
      // Create a ClientPrintJob
      const cpj = new window.JSPM.ClientPrintJob()
      
      // Set Printer type
      if (barcodeDialog.value.useDefaultPrinter) {
        cpj.clientPrinter = new window.JSPM.DefaultPrinter()
      } else {
        cpj.clientPrinter = new window.JSPM.InstalledPrinter(barcodeDialog.value.selectedPrinter)
      }
      
      // Set content to print - Create Zebra ZPL commands for 2" x 1" label (406 x 203 dots at 203 DPI)
      let cmds = "^XA"
      
      // Set label dimensions for 2" x 1" at 203 DPI
      cmds += "^PW406"  // Print width 406 dots (2 inches * 203 DPI)
      cmds += "^LL203"  // Label length 203 dots (1 inch * 203 DPI)
      
      // Set print density for darker printing
      cmds += "^MD30"   // Media darkness (0-30, higher = darker)
      cmds += "^SD15"   // Set darkness (0-30, higher = darker)
      
      // Company Logo on the left side (scaled down)
      cmds += "^FO5,5^GFA,13932,13932,43,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,gX03F8,gW0IFCK03FF,gV0JFCK07FFE,gU07JFCK0KF8,gT01KFCK0KFC,gT07KFCK0LF,gT0LFCK0LFC,gS03LFCK0LFC,gS03LFCK0MF,gS07LFCK0MF,gS0MFCK0MF8,gS0MFCK0MF8,gS0MFCK0MF8,gS0MFCK0MF8,gR01MFCK0MF8,gR01MFCK0MF,gM02J01MFCK0MF,gL07FEI01MFCK0LFC,gK01IF8001MFCK0LF8K03C,gK07IF8001MFCK0KFEL07E,gJ01JFC001MFCK0KF8K03FF8,gJ03JFE001MFCK0KFL07IF,gJ0KFE001MFCK0JF8L0JF,gI01KFE001MFCK07IFL03JFC,gI07KFC001MFCK07FFM07JFE,gI0LFC001MFCK07FCM0KFE,gH01LFC003MFCK03EM01KFE,gH07LF8003MFCT07KFC,gH07LF8003MFCS01LF8,gG01MF8003MFCS03LF8,gG01MF8007MF8S0MF8,gG01MF8007MF8R01MF,gH0MFI07MF8R07LFE,gH0MFI07MF8Q01MFC,gH07LFI07MF8Q07MF8,gH03KFEI0NF8P03NF,gH01KFEI0NFQ07NF,gI0KFE001NFP07OF,gI07JFC001MFEP0OFE,gI03JFC001MFEO0PFC,gI01JF8003MFEN0QFC,gJ0JF8003MFCL03RFC,gJ07IFI03MFCI07UFC,gJ03IFI07MFCI0VF8,gJ01FFEI0NFCI0VF,gK0FFEI0NF8001UFE,W03M03FCI0NF8001UFE,W078L01F8001NF8001UFE,V01FCM0FI01NFI01UFC,V01FEQ03NFI03UF8,V03FFQ03NFI03UF,V07FF8P07NFI07TF8,V07FFCP07MFEI07TF,V0IFEP0NFEI07TF,U01JFO01NFCI07TF,U03JF8N01NFCI0TFE,U03JFCN03NF8I0TF8,U07JFEN07NF8001SFE,U0LFN07NF8001SFC,U0LF8M0OFI03SF8T02,T01LFCL01NFEI03RF8U03,T03LFEL03NFEI03RFV03,T03MFL03NFCI07QF8V07,T07MF8K0OFCI07PFEW0FC,T07MF8K0OF8I0QF8V01FC,T07MFCJ01OF8I0PFCW03FC,T0NFEJ03OFJ0PF8W07FF,T0OFJ07OFI01OFY07FF,T0OFJ0OFEI03NF8Y07FF,T0OF8I0OFCI03MFgG07FF,T0OFCI0OFCI01JFCgH01IF,T0OFEI07NF8gQ01IF,T0OFEI07NFgS0IF,T07OFI03NFgS0FFE,T07OF8001MFEgT03C,T07OF8001MFCgT01C,T03OFCI0MF8,T01OFCI0MF8,U0OFEI07LF,U0OFEI07KFE,U03OFI03KFC,U03OFI03KFC,U01OF8001KF8,V0OF8001KF,V07NFCI0JFE,V03NFCI0JFC,V03NFEI0JF8,V01OFI07IFS03FL08,W0OFI07FFES07F1F0783EF8,W07NFI07FFCT0F3B8DC77DCO038,R01CI07NF8003FF8T0E31986438CO07FE,R07EI03NF8003FFT01C31986C30CO0JFC,Q01FFI01NFC001FET0383198CC30CN01MFC,Q03FF8001NFC001FCT07F1F0FCC30CN03NFC,Q07FFCI0NFC001F8T07F0E0784104N07NFE,Q0IFCI0NFEI0EgT0PF8,Q0IFEI07MFEgX0PF8,P01JFI07MFEgX0PF8,P01JFI03MFEgX0PFC,P03JF8003NFgX0PFC,P03JFC001NFgX0PFE,P03JFC001NF8gV01PFE,P07JFEI0NF8gV07QF,P07JFEI0NF8gV07QF,P0LFI0NF8gU01RF,P0LFI07MF8gU01RF,P0LF8007MFCgU01RF,P0LF8007MFCgU01RF8,P0LF8003MFEgU03RF8,P0LF8003MFEgU07RFC,P0LFC001MFEgV0RFC,O01LFC001MFEgV07QFC,O01LFE001MFEgV03QFC,O01LFE001MFEgW07PFC,O01LFEI0NFgW03PFC,O01LFEI0NFgX01OFC,O01LFEI0NFgY03NFC,O01MFI0NFh01MFC,O01LFEI0NFhH01KFC,P0LFEI07MFhI01IFE,P07KF8I07MF,g07MF,g07MF,g07MF,g03MF,g03MF,g03MFgR018,g03MFgR03FC,g03MFgR07FE,g03MF8gQ07IF8,g03MF8gQ0JFE,g03MF8gQ0KFC,g01MF8gP01KFE,gG07LFgQ03LF,gG01LFgQ03LF,gI0KFgQ03LF,gI01JFgQ03LF,gJ03IFgQ03LF,gK07FFgQ03LF,gK03FFgQ07LF,gL07FgQ0MF,R0FS01FgQ0MF,P0MFP02gQ0MF,P0NFCh0MFJ03KFC,O01OFCgY07LFJ07KFC,O01PFEgX07LF8I07KFC,O01QFCgW03LF8I07KFC,O01RF8gV03LF8I07KFC,O01SFgV03LFCI03KFC,O01SF8gU03LFCI03KFC,P0TFgU03LFCI03KFC,P0TFCgT01LFCI01KFC,P0UFgT01LFEI01KFC,P0UFCgS01LFEI01KFC,P0VFgS01LFEI01KFC,P0VFCgR01MFI01KF8,P0VFEgR01MF8I0KF8,P07VF8gR0MF8I07JF,P07VFEgR0MF8I07JF,P07WFgR07LF8I07JF,P03WFCgQ07LF8I03JF,P03WFEgQ07LFCI01JF,P03XF8gP07LFEI01IFE,P01XFCgP07LFEJ0IFE,P01YFgP07MFJ0IFC,Q0YF8gO07MFJ07FFC,Q0YFCgO03MFJ03FF8,Q07XFEgO03MF8I03FF8,Q03YFgO01MFCI01FE,Q01YF8gN01MFCJ0F8,R07XFCgI01EI01MFEJ07,S0XFEgI03EJ0NF,U0WF8gH07EJ0NF8,V07UF8gG01FEJ07MF8,X07SFCgG01FEJ07MFC,Y0SFEgG07FEJ03MFC,g0SFgG07FFJ03MFE,g03RF8g0IF8I03NF,gG07QFCg0IF8I01NF,gH0QFEY03IF8J0NF8,gH07QFY07IFCJ0NFC,gI0QFY0JFCJ0NFE,gI07OFEX01KFJ07NF,gJ0NFEY01KFJ03NF8,gJ07MFCY01KFJ03NFC,U0F8M01LFEg03KF8I01NFC,T03FFCM0LF8g07KFCI01NFE,T03IF8L07JFEgG0LFCI01OF,T07JFL01JF8gG0LFCJ07NF8,T07JFCL0JFgG03LFEJ07NF8,T07KFL07FFgH03LFEJ03NF8,T07KFCK01FEgH03MF8I01NF8,T07LFL0F8gH07MF8I01NF8,T07LFCK02gI0NF8J0NF8,T07MFgN01NFK07MF8,T07MFgN03MFEK03MF8,T07MF8gM03MFEK03MF8,T07MF8R03T03MFCK01MF8,T03MFR01CT07MFM0MF,T03LFEQ03FCT07MFM07KFE,T01LFCQ07FET0MFCM03KFE,U0LF8P0JFS01MFCM03KFC,U0LFP01IFES01MF8M01KFC,U07JFEP0JFCS01MFO0KF8,U03JFCO03JF8S03LFEO07JF,U03JF8O0KFT07LFEO03JF,U01JF8N07JFET07LFEO01JF,V0IFEO07JFET0MFCP0IFC,V07FFCN01KFES01MF8P07FFC,V07FF8N03KFES03MFQ03FF8,V03FFO0LFES03MFR0FF,V01FEN03LFES03LFER07E,W0FCN07LFCS03LFCR03E,W0F8M01MF8S03LFCR01C,gL03MFT03LFC,gL07LFCT0MFCI01C,gK01MFCT0MFJ03E,gK03MF8T0MFJ03F,gK07MF8T0MFJ07FC,gK0NF8T0MFJ0FFE,gJ01NFU0LFEJ0IF,gJ03MFEU0LFEI01IF8,gJ07MFCT01LFCI01IFC,gI01NF8T03LFCI03JF,gI01NF8T07LFCI03JF,gI07NF8T07LF8I03JF8,gI07NFU07LF8I07JFC,gI0NFEU07LF8I07JFE,gH01NFEU07LF8I07KF,gH03NFEU07LF8I0LF8,gH03NFEU0MF8I0LFC,gH03NF8U0MFI01LFC,gH03NFU01LFEI01LFC,gH03MFEU01LFEI01LF8,gI0MFCU01LFEI01KFE,gI07LF8U01LFEI01KFC,gI01LF8U01LFEI01KF8,gI01LFV01LFEI03KF,gJ03JFCV01LFEI03JFC,gK0JFCV01LFCI03JF,gK07IFW01LFCI03IFE,gK01FFEW01LFCI03IF8,gL07FCW03LFCI01IF,hL03LFCJ07F,hL03LFCJ03E,hL03LFC,hL03LFC,hL03LFC,hL03LF8,hL03LF8,hL03LF8,hL03LF,hL03LF,hL03KFE,hL03KFE,hL03KF8,hL03JFE,hL03JF,hL03IF,hL01FFC,hL01,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,^FS"
      
      // Asset tag at top right (moved to accommodate logo)
      cmds += `^FO70,20^A0N,24,24^FD${props.asset.asset_tag}^FS`
      
      // Serial number or asset tag as barcode (Code 128) - moved right
      cmds += "^FO70,55^BY2,2,35"  // Slightly smaller to fit with logo
      cmds += "^BCN,35,Y,N,N"
      cmds += `^FD${props.asset.serial_number || props.asset.asset_tag}^FS`
      
      // QR Code on the right side - with padding
      cmds += "^FO320,20^BQN,2,4"  // Moved slightly right
      cmds += `^FDMM,${props.asset.asset_tag}^FS`
      
      // Date at bottom left (below logo area)
      cmds += `^FO70,175^A0N,18,18^FD${new Date().toISOString().split('T')[0]}^FS`
      
      cmds += "^XZ"
      
      cpj.printerCommands = cmds
      
      // Send print job to printer!
      cpj.sendToClient()
      
      barcodeDialog.value.status = 'Print job sent successfully!'
      
      // Close dialog after successful print
      setTimeout(() => {
        barcodeDialog.value.show = false
        barcodeDialog.value.printing = false
      }, 2000)
      
    } catch (error) {
      console.error('Print error:', error)
      barcodeDialog.value.status = 'Error occurred while printing'
      barcodeDialog.value.printing = false
    }
  } else {
    barcodeDialog.value.printing = false
  }
}
</script>

<style>
/* Add any custom styles if needed */
</style> 