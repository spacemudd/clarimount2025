<template>
  <div class="barcode-scanner">
    <Dialog v-model:open="isOpen" @update:open="handleDialogChange">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ title || 'Scan Barcode' }}</DialogTitle>
          <DialogDescription>
            {{ description || 'Position the barcode within the camera view to scan it automatically.' }}
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4">
          <!-- Camera View -->
          <div class="relative">
            <video 
              ref="videoElement"
              class="w-full h-64 bg-black rounded-lg"
              autoplay
              muted
              playsinline
            ></video>
            
            <!-- Scanning overlay -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="border-2 border-red-500 w-48 h-24 rounded-lg opacity-50">
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-red-500"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-red-500"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-red-500"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-red-500"></div>
              </div>
            </div>
            
            <!-- Status overlay -->
            <div v-if="status" class="absolute bottom-2 left-2 right-2 bg-black bg-opacity-75 text-white text-sm p-2 rounded">
              {{ status }}
            </div>
          </div>
          
          <!-- Manual input fallback -->
          <div class="space-y-2">
            <Label for="manual-input">Or enter manually:</Label>
            <Input
              id="manual-input"
              v-model="manualInput"
              type="text"
              placeholder="Enter barcode value manually"
              @keyup.enter="handleManualInput"
            />
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="handleCancel">
            Cancel
          </Button>
          <Button 
            @click="handleManualInput" 
            :disabled="!manualInput.trim()"
          >
            Use Manual Input
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { BrowserMultiFormatReader, BrowserCodeReader } from '@zxing/browser'
import { Result } from '@zxing/library'

interface Props {
  modelValue: boolean
  title?: string
  description?: string
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'scanned', value: string): void
  (e: 'cancel'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isOpen = ref(props.modelValue)
const videoElement = ref<HTMLVideoElement | null>(null)
const status = ref('')
const manualInput = ref('')

let codeReader: BrowserMultiFormatReader | null = null
let stream: MediaStream | null = null

watch(() => props.modelValue, (newValue) => {
  isOpen.value = newValue
  if (newValue) {
    startScanning()
  } else {
    stopScanning()
  }
})

const handleDialogChange = (open: boolean) => {
  emit('update:modelValue', open)
  if (!open) {
    stopScanning()
  }
}

const startScanning = async () => {
  try {
    status.value = 'Initializing camera...'
    
    // Initialize the code reader
    codeReader = new BrowserMultiFormatReader()
    
    // Get available video devices
    const videoDevices = await BrowserCodeReader.listVideoInputDevices()
    
    if (videoDevices.length === 0) {
      status.value = 'No camera found. Please use manual input.'
      return
    }
    
    // Prefer back camera on mobile devices
    const backCamera = videoDevices.find((device: any) => 
      device.label.toLowerCase().includes('back') || 
      device.label.toLowerCase().includes('rear')
    )
    
    const selectedDevice = backCamera || videoDevices[0]
    
    status.value = 'Starting camera...'
    
    // Start decoding from video element
    if (videoElement.value) {
      const controls = await codeReader.decodeFromVideoDevice(
        selectedDevice.deviceId,
        videoElement.value,
        (result: Result | undefined, error: Error | undefined) => {
          if (result) {
            const scannedValue = result.getText()
            status.value = `Scanned: ${scannedValue}`
            
            // Emit the scanned value
            emit('scanned', scannedValue)
            
            // Close the dialog
            setTimeout(() => {
              handleCancel()
            }, 1000)
          }
          
          if (error) {
            // Don't log common "not found" errors as they're expected during scanning
            if (!error.message.includes('No MultiFormat Readers were able to detect the code')) {
              console.warn('Barcode scanning error:', error)
            }
          }
        }
      )
      
      // Store controls for stopping later
      stream = controls as any
      
      status.value = 'Ready to scan. Position barcode in the frame.'
    }
  } catch (error) {
    console.error('Failed to start barcode scanning:', error)
    status.value = 'Failed to access camera. Please check permissions and use manual input.'
  }
}

const stopScanning = () => {
  if (codeReader) {
    // Stop the decoding process
    try {
      // The controls object returned from decodeFromVideoDevice has a stop method
      if (stream && (stream as any).stop) {
        (stream as any).stop()
      }
    } catch (error) {
      console.warn('Error stopping barcode scanner:', error)
    }
    codeReader = null
  }
  
  if (stream) {
    // If stream has getTracks method, it's a MediaStream
    if ((stream as any).getTracks) {
      (stream as any).getTracks().forEach((track: any) => track.stop())
    }
    stream = null
  }
  
  status.value = ''
  manualInput.value = ''
}

const handleManualInput = () => {
  if (manualInput.value.trim()) {
    emit('scanned', manualInput.value.trim())
    handleCancel()
  }
}

const handleCancel = () => {
  emit('cancel')
  emit('update:modelValue', false)
  stopScanning()
}

onMounted(() => {
  if (props.modelValue) {
    startScanning()
  }
})

onUnmounted(() => {
  stopScanning()
})
</script>

<style scoped>
.barcode-scanner video {
  object-fit: cover;
}
</style> 