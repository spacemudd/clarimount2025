<template>
  <Dialog v-model:open="isOpen" @update:open="handleDialogChange">
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>Print Multiple Asset Labels</DialogTitle>
        <DialogDescription>
          Configure printer settings for bulk printing {{ assets.length }} asset labels.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-4">
        <!-- Assets list -->
        <div class="max-h-40 overflow-y-auto border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
          <h4 class="font-medium text-sm mb-2">Assets to print:</h4>
          <div class="space-y-1">
            <div 
              v-for="asset in assets" 
              :key="asset.id"
              class="flex justify-between text-sm"
            >
              <span class="font-mono">{{ asset.asset_tag }}</span>
              <span class="text-gray-500">{{ asset.serial_number || 'No S/N' }}</span>
            </div>
          </div>
        </div>
        
        <!-- Printer settings -->
        <div class="flex items-center space-x-2">
          <input 
            type="checkbox" 
            id="useDefaultPrinter" 
            v-model="useDefaultPrinter"
            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          />
          <Label for="useDefaultPrinter" class="font-medium">
            Print to Default printer
          </Label>
        </div>
        
        <div v-if="!useDefaultPrinter" class="space-y-2">
          <Label for="installedPrinterName">Select an installed Printer:</Label>
          <select 
            id="installedPrinterName" 
            v-model="selectedPrinter"
            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          >
            <option value="">Select a printer...</option>
            <option v-for="printer in availablePrinters" :key="printer" :value="printer">
              {{ printer }}
            </option>
          </select>
        </div>

        <div v-if="status" class="text-sm text-gray-600 dark:text-gray-400">
          Status: {{ status }}
        </div>
        
        <!-- Progress indicator -->
        <div v-if="printing && printProgress > 0" class="space-y-2">
          <div class="flex justify-between text-sm">
            <span>Printing progress:</span>
            <span>{{ printProgress }} / {{ assets.length }}</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
              :style="{ width: `${(printProgress / assets.length) * 100}%` }"
            ></div>
          </div>
        </div>
      </div>
      
      <DialogFooter>
        <Button variant="outline" @click="handleCancel">
          Cancel
        </Button>
        <Button 
          @click="printAllBarcodes" 
          :disabled="printing || (!useDefaultPrinter && !selectedPrinter)"
        >
          <Icon v-if="printing" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
          Print All Labels
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import Icon from '@/components/Icon.vue'

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

interface Asset {
  id: number
  asset_tag: string
  serial_number?: string
  company_name: string
  category?: { name: string }
  assetTemplate?: { name: string }
}

interface Props {
  modelValue: boolean
  assets: Asset[]
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'completed'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isOpen = ref(props.modelValue)
const printing = ref(false)
const printProgress = ref(0)
const useDefaultPrinter = ref(false)
const selectedPrinter = ref('')
const availablePrinters = ref<string[]>([])
const status = ref('')

const handleDialogChange = (open: boolean) => {
  emit('update:modelValue', open)
}

const handleCancel = () => {
  if (!printing.value) {
    emit('update:modelValue', false)
  }
}

// Function to dynamically load JavaScript files
const loadScript = (src: string): Promise<void> => {
  return new Promise((resolve, reject) => {
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

const loadRequiredScripts = async (): Promise<void> => {
  try {
    status.value = 'Loading print libraries...'
    
    await loadScript('/js/bluebird.min.js')
    await loadScript('/js/jquery-3.2.1.slim.min.js') 
    await loadScript('/js/jsmanager/JSPrintManager.js')
    
    status.value = 'Print libraries loaded successfully'
  } catch (error) {
    console.error('Failed to load scripts:', error)
    status.value = 'Failed to load print libraries'
  }
}

const initializeJSPrintManager = () => {
  nextTick(() => {
    if (typeof window.JSPM !== 'undefined') {
      window.JSPM.JSPrintManager.auto_reconnect = true
      window.JSPM.JSPrintManager.start()
      window.JSPM.JSPrintManager.WS.onStatusChanged = function () {
        if (jspmWSStatus()) {
          status.value = 'Connected to JSPrintManager'
          window.JSPM.JSPrintManager.getPrinters().then(function (myPrinters: string[]) {
            availablePrinters.value = myPrinters
          })
        }
      }
    } else {
      status.value = 'JSPrintManager not loaded'
    }
  })
}

const jspmWSStatus = () => {
  if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Open) {
    return true
  } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Closed) {
    status.value = 'JSPrintManager is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm'
    return false
  } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Blocked) {
    status.value = 'JSPM has blocked this website!'
    return false
  }
  return false
}

const generateZPLForAsset = (asset: Asset): string => {
  let cmds = "^XA"
  
  // Set label dimensions for 2" x 1" at 203 DPI
  cmds += "^PW406"  // Print width 406 dots (2 inches * 203 DPI)
  cmds += "^LL203"  // Label length 203 dots (1 inch * 203 DPI)
  
  // Set print density for darker printing
  cmds += "^MD30"   // Media darkness (0-30, higher = darker)
  cmds += "^SD15"   // Set darkness (0-30, higher = darker)
  
  // Company logo on the left side (centered with top margin)
  cmds += "^FO10,50^GFA,329,329,7,,,,,,K0F1C,J01E3E,I039F1C4,I079E10F,I07BF03E,I073E17E,00213E7FC,00607CFF8,00F87CFF,00F8F9F800C,00FCF8J08,007EF,003E6,03BEK03F,039FK07F,039FK07F,07CFK03F8,008F,I0F8I018,I07J03C,03DK03E68,07FCJ01C7,03FFJ01E7,03FF8I01F3,017FEI09F,I0FE0018F8,0043E003CF8,00F1I03C7C,00F80807C3C,0070700F838,0020F00F008,I03E00F3,I07E01F38,I07C01E78,I03801E3,L01E,L01C,L01,,,,,^FS"
  
  // Company name above asset tag (small text)
  cmds += "^FO70,30^A0N,18,18^FDFAHAD NAWAF ALZEER HOLDING CO.^FS"
  
  // Asset tag next to logo (with double margin under company name)
  cmds += `^FO70,65^A0N,24,24^FD${asset.asset_tag}^FS`
  
  // Asset tag as barcode (Code 128) - always use asset_tag, not serial_number
  cmds += "^FO70,100^BY2,2,35"
  cmds += "^BCN,35,Y,N,N"
  cmds += `^FD${asset.asset_tag}^FS`
  
  // QR Code on the right side - centered with top margin
  cmds += "^FO320,50^BQN,2,4"
  cmds += `^FDMM,${asset.asset_tag}^FS`
  
  // Date and category at bottom left (below barcode)
  const currentDate = new Date().toISOString().split('T')[0]
  const categoryName = asset.category?.name || asset.assetTemplate?.name || 'Unknown'
  cmds += `^FO70,175^A0N,18,18^FD${currentDate} - ${categoryName}^FS`
  
  cmds += "^XZ"
  
  return cmds
}

const printAllBarcodes = async () => {
  if (!jspmWSStatus()) {
    return
  }

  printing.value = true
  printProgress.value = 0
  
  try {
    for (let i = 0; i < props.assets.length; i++) {
      const asset = props.assets[i]
      
      // Create a ClientPrintJob
      const cpj = new window.JSPM.ClientPrintJob()
      
      // Set Printer type
      if (useDefaultPrinter.value) {
        cpj.clientPrinter = new window.JSPM.DefaultPrinter()
      } else {
        cpj.clientPrinter = new window.JSPM.InstalledPrinter(selectedPrinter.value)
      }
      
      // Set ZPL commands for this asset
      cpj.printerCommands = generateZPLForAsset(asset)
      
      // Send print job to printer
      cpj.sendToClient()
      
      // Update progress
      printProgress.value = i + 1
      status.value = `Printing ${printProgress.value} of ${props.assets.length}...`
      
      // Small delay between print jobs to avoid overwhelming the printer
      if (i < props.assets.length - 1) {
        await new Promise(resolve => setTimeout(resolve, 500))
      }
    }
    
    status.value = `Successfully printed ${props.assets.length} labels!`
    
    // Close dialog after successful print
    setTimeout(() => {
      emit('completed')
      emit('update:modelValue', false)
      printing.value = false
      printProgress.value = 0
    }, 2000)
    
  } catch (error) {
    console.error('Bulk print error:', error)
    status.value = 'Error occurred while printing'
    printing.value = false
    printProgress.value = 0
  }
}

onMounted(async () => {
  if (props.modelValue) {
    if (typeof window.JSPM === 'undefined') {
      await loadRequiredScripts()
    }
    initializeJSPrintManager()
  }
})
</script> 