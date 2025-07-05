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
          
          <!-- Camera info -->
          <div v-if="availableCameras.length > 0" class="text-xs text-gray-600 dark:text-gray-400 text-center">
            Camera: {{ availableCameras[selectedCameraIndex]?.label || 'Unknown' }}
            <span v-if="availableCameras.length > 1">({{ selectedCameraIndex + 1 }}/{{ availableCameras.length }})</span>
          </div>
          
          <!-- Samsung device tip -->
          <div v-if="isSamsungDevice" class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-center">
            <div class="flex items-center justify-center gap-2 mb-1">
              <span class="text-sm">📱</span>
              <strong>Samsung S24+ detected</strong>
            </div>
            <div class="space-y-1">
              <div>• Tap the camera view to focus if image is blurry</div>
              <div>• Double-tap for enhanced focus</div>
              <div>• Hold barcode 15-20cm from camera for best results</div>
            </div>
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
              @click="switchCamera"
              v-if="availableCameras.length > 1"
            >
              📹 Switch Camera
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="restartScanning"
            >
              🔄 Restart
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
  enableCameraCapture?: boolean
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
let availableCameras = ref<MediaDeviceInfo[]>([])
let selectedCameraIndex = ref(0)

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
    
    // Store available cameras
    availableCameras.value = videoDevices
    
    // Debug: log available cameras
    console.log('Available cameras:', videoDevices.map(d => ({ id: d.deviceId, label: d.label })))
    
    // If this is the first time, try to find back camera
    if (selectedCameraIndex.value === 0) {
      // Prefer back camera on mobile devices - improved detection
      const backCameraIndex = videoDevices.findIndex((device: any) => {
        const label = device.label.toLowerCase()
        return label.includes('back') || 
               label.includes('rear') ||
               label.includes('environment') ||
               label.includes('camera2') ||
               label.includes('facing back') ||
               label.includes('world') ||
               // Samsung specific back camera identifiers
               (label.includes('camera') && label.includes('1')) ||
               // Generic back camera patterns
               label.match(/camera.*[^0]$/) // Not ending with 0 (front camera)
      })
      
      // If back camera found, use it; otherwise use last camera (usually back on mobile)
      selectedCameraIndex.value = backCameraIndex !== -1 ? backCameraIndex : videoDevices.length - 1
    }
    
    // Use the selected camera
    const selectedDevice = videoDevices[selectedCameraIndex.value]
    
    console.log('Selected camera:', { id: selectedDevice.deviceId, label: selectedDevice.label })
    status.value = `Starting camera: ${selectedDevice.label || 'Unknown camera'}...`
    
    // Detect Samsung device - improved detection
    const userAgent = navigator.userAgent.toLowerCase()
    const isSamsung = userAgent.includes('samsung') || userAgent.includes('sm-') || userAgent.includes('galaxy')
    isSamsungDevice.value = isSamsung
    
    // Configure video constraints with proper autofocus for Samsung S24+
    const videoConstraints: any = {
      deviceId: selectedDevice.deviceId,
      width: isSamsung ? { ideal: 1920, min: 1280 } : { ideal: 1920, min: 1280 },
      height: isSamsung ? { ideal: 1080, min: 720 } : { ideal: 1080, min: 720 },
      frameRate: { ideal: 30, min: 15 },
      // Proper autofocus constraints for Samsung devices
      focusMode: isSamsung ? 'continuous' : 'continuous',
      // Advanced settings for Samsung S24+
      ...(isSamsung && {
        // These are the correct constraint names for Samsung devices
        advanced: [{
          focusMode: 'continuous',
          exposureMode: 'continuous',
          whiteBalanceMode: 'continuous',
          // Samsung-specific autofocus settings
          focusDistance: 0.3, // Optimal distance for barcode scanning
          torch: false, // Disable torch for better autofocus
        }]
      })
    }
    
    if (isSamsung) {
      status.value = 'Starting camera (Samsung S24+ optimized)...'
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
              
              // For camera capture mode, emit the image data
              if (props.enableCameraCapture) {
                captureCurrentFrame()
              } else {
                // Emit the scanned value
                emit('scanned', scannedValue)
                
                // Close the dialog
                setTimeout(() => {
                  handleCancel()
                }, 1500)
              }
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
              
              if (props.enableCameraCapture) {
                captureCurrentFrame()
              } else {
                emit('scanned', scannedValue)
                setTimeout(() => handleCancel(), 1500)
              }
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

const captureCurrentFrame = () => {
  if (videoElement.value) {
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')
    
    if (context) {
      canvas.width = videoElement.value.videoWidth
      canvas.height = videoElement.value.videoHeight
      context.drawImage(videoElement.value, 0, 0)
      
      const imageData = canvas.toDataURL('image/jpeg', 0.8)
      emit('scanned', imageData)
      
      setTimeout(() => {
        handleCancel()
      }, 1500)
    }
  }
}

const optimizeForSamsung = (videoElement: HTMLVideoElement) => {
  // Samsung S24+ specific camera optimizations
  try {
    // Enhanced tap-to-focus functionality for Samsung devices
    const handleTapToFocus = async (event?: Event) => {
      if (videoElement.srcObject) {
        const videoStream = videoElement.srcObject as MediaStream
        const videoTrack = videoStream.getVideoTracks()[0]
        
        if (videoTrack && videoTrack.applyConstraints) {
          try {
            // Get current capabilities to see what's actually supported
            const capabilities = videoTrack.getCapabilities ? videoTrack.getCapabilities() : {} as any
            console.log('Camera capabilities:', capabilities)
            
            // Try to apply focus constraints that are actually supported
            const constraints: any = {}
            
            // Only apply constraints that are supported
            if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
              constraints.focusMode = 'continuous'
            }
            
            if (capabilities.exposureMode && capabilities.exposureMode.includes('continuous')) {
              constraints.exposureMode = 'continuous'
            }
            
            if (capabilities.whiteBalanceMode && capabilities.whiteBalanceMode.includes('continuous')) {
              constraints.whiteBalanceMode = 'continuous'
            }
            
            // Apply supported constraints
            if (Object.keys(constraints).length > 0) {
              await videoTrack.applyConstraints(constraints)
              console.log('Applied focus constraints:', constraints)
            }
            
            // Alternative approach: restart the track to trigger refocus
            if (event) {
              console.log('Manual focus trigger - restarting track')
              
              // Stop and restart the track to force refocus
              const devices = await navigator.mediaDevices.enumerateDevices()
              const videoDevices = devices.filter(device => device.kind === 'videoinput')
              const currentDevice = videoDevices[selectedCameraIndex.value]
              
              if (currentDevice) {
                // Get fresh stream with focus trigger
                const newStream = await navigator.mediaDevices.getUserMedia({
                  video: {
                    deviceId: currentDevice.deviceId,
                    width: { ideal: 1920, min: 1280 },
                    height: { ideal: 1080, min: 720 },
                    frameRate: { ideal: 30, min: 15 }
                  }
                })
                
                // Replace the video source
                videoElement.srcObject = newStream
                
                // Update our stream reference
                if (videoStream) {
                  videoStream.getTracks().forEach(track => track.stop())
                }
                
                // Update the global stream reference
                stream = newStream as any
                
                status.value = 'Camera refocused!'
                setTimeout(() => {
                  status.value = 'Ready to scan. Position QR code (blue square) or barcode (red rectangle) in the frame.'
                }, 2000)
              }
            }
            
          } catch (error) {
            console.warn('Focus constraints failed, trying alternative method:', error)
            
            // Fallback: try to restart the camera with basic constraints
            try {
              await videoTrack.applyConstraints({
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                frameRate: { ideal: 30 }
              })
              console.log('Applied basic constraints for focus')
            } catch (fallbackError) {
              console.warn('All focus methods failed:', fallbackError)
            }
          }
        }
      }
    }
    
    // Add click event for manual focus trigger
    videoElement.addEventListener('click', handleTapToFocus)
    
    // Add touch events for better mobile support
    videoElement.addEventListener('touchstart', (e) => {
      e.preventDefault() // Prevent default touch behavior
      handleTapToFocus(e)
    })
    
    // Add double-tap for enhanced focus
    let lastTap = 0
    const handleDoubleTap = (e: Event) => {
      const now = Date.now()
      if (now - lastTap < 300) {
        // Double tap detected - trigger enhanced focus
        e.preventDefault()
        console.log('Double tap detected - enhanced focus')
        handleTapToFocus(e)
        setTimeout(() => handleTapToFocus(e), 500) // Second focus attempt
      }
      lastTap = now
    }
    
    videoElement.addEventListener('touchend', handleDoubleTap)
    videoElement.addEventListener('click', handleDoubleTap)
    
    // Trigger focus every 5 seconds for Samsung devices (less aggressive)
    const focusInterval = setInterval(() => handleTapToFocus(), 5000)
    
    // Set Samsung S24+ specific video properties for better barcode visibility
    videoElement.style.filter = 'contrast(1.2) brightness(1.15) saturate(1.05)'
    
    // Add visual feedback for Samsung users
    const addTapIndicator = () => {
      const indicator = document.createElement('div')
      indicator.innerHTML = '👆 Tap anywhere to focus • Double-tap for enhanced focus'
      indicator.style.cssText = `
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        text-align: center;
        z-index: 10;
        pointer-events: none;
        animation: fadeInOut 8s ease-in-out;
      `
      
      // Add CSS animation
      const style = document.createElement('style')
      style.textContent = `
        @keyframes fadeInOut {
          0% { opacity: 0; transform: translateY(-10px); }
          10% { opacity: 1; transform: translateY(0); }
          85% { opacity: 1; transform: translateY(0); }
          100% { opacity: 0; transform: translateY(-10px); }
        }
      `
      document.head.appendChild(style)
      
      videoElement.parentElement?.appendChild(indicator)
      
      // Remove indicator after animation
      setTimeout(() => {
        indicator.remove()
        style.remove()
      }, 8000)
    }
    
    // Show tap indicator after 1 second
    setTimeout(addTapIndicator, 1000)
    
    // Initial focus trigger after 1 second
    setTimeout(() => handleTapToFocus(), 1000)
    
    // Clear interval and event listeners when video ends or component unmounts
    const cleanup = () => {
      clearInterval(focusInterval)
      videoElement.removeEventListener('click', handleTapToFocus)
      videoElement.removeEventListener('touchstart', handleTapToFocus)
      videoElement.removeEventListener('touchend', handleDoubleTap)
      videoElement.removeEventListener('click', handleDoubleTap)
    }
    
    videoElement.addEventListener('ended', cleanup, { once: true })
    
    // Store cleanup function for component unmount
    ;(videoElement as any).__samsungCleanup = cleanup
    
  } catch (error) {
    console.warn('Samsung S24+ optimization failed:', error)
  }
}

const handleCancel = () => {
  emit('cancel')
  emit('update:modelValue', false)
  stopScanning()
}

const switchCamera = () => {
  if (availableCameras.value.length > 1) {
    // Cycle to next camera
    selectedCameraIndex.value = (selectedCameraIndex.value + 1) % availableCameras.value.length
    
    // Restart scanning with new camera
    restartScanning()
  }
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