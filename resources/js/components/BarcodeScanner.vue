<template>
  <div class="barcode-scanner">
    <Dialog v-model:open="isOpen" @update:open="handleDialogChange">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ title || 'Scan Barcode/QR Code' }}</DialogTitle>
          <DialogDescription>
            {{ description || 'Position the barcode or QR code within the camera view to scan it automatically. Supports 1D barcodes, QR codes, and other 2D formats.' }}
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
              style="object-fit: cover;"
            ></video>
            
            <!-- Scanning overlay - Square for QR codes, Rectangle for barcodes -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <!-- QR Code frame (square) -->
              <div class="border-2 border-blue-500 w-48 h-48 rounded-lg opacity-60">
                <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-blue-500"></div>
                <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-blue-500"></div>
                <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-blue-500"></div>
                <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-blue-500"></div>
              </div>
              <!-- Barcode frame (rectangle) -->
              <div class="border-2 border-red-500 w-56 h-16 rounded-lg opacity-40 absolute">
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
          
          <!-- Samsung device tip -->
          <div v-if="isSamsungDevice" class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 p-2 rounded text-center">
            <strong>Samsung device detected:</strong> Tap the camera view to focus if image is blurry
          </div>
          
          <!-- Scanning mode toggle -->
          <div class="flex gap-2 justify-center">
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="toggleScanMode"
              :class="{ 'bg-blue-100 border-blue-500': isQRMode }"
            >
              {{ isQRMode ? 'QR Code Mode' : 'All Formats' }}
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="restartScanning"
            >
              Restart Camera
            </Button>
          </div>

          <!-- Manual input fallback -->
          <div class="space-y-2">
            <Label for="manual-input">Or enter manually:</Label>
            <Input
              id="manual-input"
              v-model="manualInput"
              type="text"
              placeholder="Enter barcode/QR code value manually"
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
import { BrowserMultiFormatReader, BrowserCodeReader, BrowserQRCodeReader } from '@zxing/browser'
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

let codeReader: BrowserMultiFormatReader | BrowserQRCodeReader | null = null
let stream: MediaStream | null = null
let isQRMode = ref(false)

// Detect Samsung device
const isSamsungDevice = ref(false)

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
    
    // Initialize the appropriate code reader based on mode
    if (isQRMode.value) {
      codeReader = new BrowserQRCodeReader()
      status.value = 'Initializing QR code scanner...'
    } else {
      codeReader = new BrowserMultiFormatReader()
      status.value = 'Initializing multi-format scanner...'
    }
    
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
    
    // Detect Samsung device
    const userAgent = navigator.userAgent.toLowerCase()
    const isSamsung = userAgent.includes('samsung') || userAgent.includes('sm-')
    isSamsungDevice.value = isSamsung
    
    // Configure video constraints optimized for Samsung devices
    const videoConstraints = {
      deviceId: selectedDevice.deviceId,
      width: isSamsung ? { ideal: 1280, min: 720 } : { ideal: 1920, min: 1280 },
      height: isSamsung ? { ideal: 720, min: 480 } : { ideal: 1080, min: 720 },
      frameRate: { ideal: 30, min: 15 },
      focusMode: 'continuous',
      exposureMode: 'continuous',
      whiteBalanceMode: 'continuous'
    }
    
    // Add Samsung-specific constraints
    if (isSamsung) {
      Object.assign(videoConstraints, {
        focusDistance: { ideal: 0.2, min: 0.1, max: 0.5 },
        torch: false, // Disable torch for better focus
        zoom: { ideal: 1.0, min: 1.0, max: 2.0 },
        // Samsung-specific optimizations
        aspectRatio: { ideal: 16/9 },
        resizeMode: 'crop-and-scale'
      })
      status.value = 'Starting camera (Samsung S25+ optimized)...'
    }

    // Start decoding from video element with enhanced constraints
    if (videoElement.value && codeReader) {
      try {
        // Try with advanced constraints first (for Samsung and modern devices)
        const controls = await codeReader.decodeFromConstraints(
          { video: videoConstraints },
          videoElement.value,
          (result: Result | undefined, error: Error | undefined) => {
            if (result) {
              const scannedValue = result.getText()
              const format = result.getBarcodeFormat()
              status.value = `Scanned ${format}: ${scannedValue.substring(0, 20)}${scannedValue.length > 20 ? '...' : ''}`
              
              // Emit the scanned value
              emit('scanned', scannedValue)
              
              // Close the dialog
              setTimeout(() => {
                handleCancel()
              }, 1500)
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
        
        status.value = 'Ready to scan. Position QR code (blue square) or barcode (red rectangle) in the frame.'
        
        // Apply Samsung-specific optimizations
        if (isSamsung && videoElement.value) {
          optimizeForSamsung(videoElement.value)
        }
        
      } catch (constraintError) {
        console.warn('Advanced constraints failed, trying basic device scanning:', constraintError)
        
        // Fallback to basic device scanning for Samsung compatibility
        const controls = await codeReader.decodeFromVideoDevice(
          selectedDevice.deviceId,
          videoElement.value,
          (result: Result | undefined, error: Error | undefined) => {
            if (result) {
              const scannedValue = result.getText()
              const format = result.getBarcodeFormat()
              status.value = `Scanned ${format}: ${scannedValue}`
              emit('scanned', scannedValue)
              setTimeout(() => handleCancel(), 1500)
            }
          }
        )
        
        stream = controls as any
        status.value = 'Camera started (compatibility mode). Hold steady and position code in frame.'
        
        // Apply Samsung optimizations to fallback mode too
        if (isSamsung && videoElement.value) {
          optimizeForSamsung(videoElement.value)
        }
      }
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
  
  // Clean up Samsung optimizations
  if (videoElement.value && (videoElement.value as any).__samsungCleanup) {
    (videoElement.value as any).__samsungCleanup()
    delete (videoElement.value as any).__samsungCleanup
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

const toggleScanMode = () => {
  isQRMode.value = !isQRMode.value
  restartScanning()
}

const restartScanning = () => {
  stopScanning()
  setTimeout(() => {
    if (isOpen.value) {
      startScanning()
    }
  }, 100)
}

const optimizeForSamsung = (videoElement: HTMLVideoElement) => {
  // Samsung-specific camera optimizations
  try {
    // Add tap-to-focus functionality for Samsung devices
    const handleTapToFocus = async () => {
      if (videoElement.srcObject) {
        const stream = videoElement.srcObject as MediaStream
        const videoTrack = stream.getVideoTracks()[0]
        
        if (videoTrack && videoTrack.applyConstraints) {
          try {
            // First try to apply enhanced constraints for Samsung
            await videoTrack.applyConstraints({
              width: { ideal: 1280, min: 720 },
              height: { ideal: 720, min: 480 },
              frameRate: { ideal: 30, min: 15 },
              aspectRatio: { ideal: 16/9 }
            })
            
            // Wait a bit then try to trigger focus
            setTimeout(async () => {
              try {
                await videoTrack.applyConstraints({
                  width: { ideal: 1280 },
                  height: { ideal: 720 },
                  frameRate: { ideal: 30 }
                })
              } catch (e) {
                console.warn('Samsung focus retry failed:', e)
              }
            }, 200)
            
          } catch (error) {
            console.warn('Could not apply Samsung focus constraints:', error)
          }
        }
      }
    }
    
    // Add click event for manual focus trigger
    videoElement.addEventListener('click', handleTapToFocus)
    
    // Trigger focus every 4 seconds for Samsung devices (less frequent to avoid conflicts)
    const focusInterval = setInterval(handleTapToFocus, 4000)
    
    // Set Samsung-specific video properties for better visibility
    videoElement.style.filter = 'contrast(1.15) brightness(1.1) saturate(1.1)'
    
    // Add visual feedback for Samsung users
    const addTapIndicator = () => {
      const indicator = document.createElement('div')
      indicator.innerHTML = '📱 Tap to focus'
      indicator.style.cssText = `
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 10;
        pointer-events: none;
      `
      videoElement.parentElement?.appendChild(indicator)
      
      // Remove indicator after 5 seconds
      setTimeout(() => {
        indicator.remove()
      }, 5000)
    }
    
    // Show tap indicator after 2 seconds
    setTimeout(addTapIndicator, 2000)
    
    // Clear interval when video ends or component unmounts
    const cleanup = () => {
      clearInterval(focusInterval)
      videoElement.removeEventListener('click', handleTapToFocus)
    }
    
    videoElement.addEventListener('ended', cleanup, { once: true })
    
    // Store cleanup function for component unmount
    ;(videoElement as any).__samsungCleanup = cleanup
    
  } catch (error) {
    console.warn('Samsung optimization failed:', error)
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