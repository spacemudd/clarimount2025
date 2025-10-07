<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">ZKTeko Debug Dashboard</h1>
              <p class="mt-2 text-sm text-gray-600">
                Monitor ZKTeko fingerprint devices and their heartbeats
              </p>
            </div>
            <div class="flex items-center space-x-4">
              <button
                @click="refreshData"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ loading ? 'Refreshing...' : 'Refresh' }}
              </button>
              <div class="text-sm text-gray-500">
                Last updated: {{ lastUpdated ? formatTime(lastUpdated) : 'Never' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Devices</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.total_devices }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Online Devices</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.online_devices }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-3 h-3 bg-red-400 rounded-full"></div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Offline Devices</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.offline_devices }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Recent Heartbeats</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.devices_with_recent_heartbeats }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Devices Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
          <h3 class="text-lg leading-6 font-medium text-gray-900">ZKTeko Devices</h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            List of all ZKTeko fingerprint devices and their current status
          </p>
        </div>
        
        <div v-if="loading && devices.length === 0" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-500">Loading devices...</p>
        </div>

        <div v-else-if="devices.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No devices found</h3>
          <p class="mt-1 text-sm text-gray-500">No ZKTeko devices have been detected yet.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="device in devices" :key="device.id" class="hover:bg-gray-50">
            <div class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :class="{
                        'bg-green-400': device.is_online && !device.is_considered_offline,
                        'bg-yellow-400': device.is_online && device.is_considered_offline,
                        'bg-red-400': !device.is_online
                      }"
                    ></div>
                  </div>
                  <div class="ml-4">
                    <div class="flex items-center">
                      <h4 class="text-lg font-medium text-gray-900">{{ device.display_name }}</h4>
                      <span 
                        class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                          'bg-green-100 text-green-800': device.status === 'online',
                          'bg-red-100 text-red-800': device.status === 'offline',
                          'bg-yellow-100 text-yellow-800': device.status === 'maintenance',
                          'bg-gray-100 text-gray-800': device.status === 'unknown'
                        }"
                      >
                        {{ device.status }}
                      </span>
                    </div>
                    <div class="mt-1">
                      <p class="text-sm text-gray-500">
                        S/N: {{ device.serial_number }}
                        <span v-if="device.model" class="ml-2">• {{ device.model }}</span>
                        <span v-if="device.ip_address" class="ml-2">• {{ device.ip_address }}</span>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="flex items-center space-x-4">
                  <div class="text-right">
                    <div class="text-sm text-gray-900">
                      {{ device.total_heartbeats }} heartbeats
                    </div>
                    <div class="text-sm text-gray-500">
                      Last: {{ device.time_since_last_heartbeat }}
                    </div>
                  </div>
                  
                  <div class="flex space-x-2">
                    <button
                      @click="viewDevice(device.id)"
                      class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      View Details
                    </button>
                    
                    <button
                      v-if="device.is_online"
                      @click="markOffline(device.id)"
                      class="inline-flex items-center px-3 py-1 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                      Mark Offline
                    </button>
                    
                    <button
                      v-if="device.last_error"
                      @click="clearError(device.id)"
                      class="inline-flex items-center px-3 py-1 border border-yellow-300 rounded-md text-sm font-medium text-yellow-700 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    >
                      Clear Error
                    </button>
                  </div>
                </div>
              </div>
              
              <div v-if="device.last_error" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <div class="mt-1 text-sm text-red-700">{{ device.last_error }}</div>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Device Details Modal -->
    <div v-if="selectedDevice" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">
                Device Details: {{ selectedDevice.serial_number }}
              </h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Device Information</h4>
                <dl class="space-y-2">
                  <div>
                    <dt class="text-sm text-gray-500">Serial Number</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.serial_number }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Device Name</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.device_name || 'N/A' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Model</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.model || 'N/A' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Firmware Version</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.firmware_version || 'N/A' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">IP Address</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.ip_address || 'N/A' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">MAC Address</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.mac_address || 'N/A' }}</dd>
                  </div>
                </dl>
              </div>
              
              <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Status Information</h4>
                <dl class="space-y-2">
                  <div>
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="text-sm">
                      <span 
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                          'bg-green-100 text-green-800': selectedDevice.status === 'online',
                          'bg-red-100 text-red-800': selectedDevice.status === 'offline',
                          'bg-yellow-100 text-yellow-800': selectedDevice.status === 'maintenance',
                          'bg-gray-100 text-gray-800': selectedDevice.status === 'unknown'
                        }"
                      >
                        {{ selectedDevice.status }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Online</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.is_online ? 'Yes' : 'No' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Last Heartbeat</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.time_since_last_heartbeat }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Total Heartbeats</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.total_heartbeats }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-gray-500">Total Attendance Records</dt>
                    <dd class="text-sm text-gray-900">{{ selectedDevice.total_attendance_records }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            
            <div v-if="selectedDevice.device_info" class="mt-6">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Raw Device Data</h4>
              <pre class="bg-gray-100 p-3 rounded-md text-xs overflow-auto max-h-40">{{ JSON.stringify(selectedDevice.device_info, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  devices: Array,
  stats: Object
})

const loading = ref(false)
const lastUpdated = ref(null)
const selectedDevice = ref(null)
let refreshInterval = null

const refreshData = async () => {
  loading.value = true
  try {
    await router.reload({ only: ['devices', 'stats'] })
    lastUpdated.value = new Date()
  } finally {
    loading.value = false
  }
}

const viewDevice = async (deviceId) => {
  try {
    const response = await fetch(`/zkteko-debug/devices/${deviceId}`)
    const data = await response.json()
    selectedDevice.value = data.device
  } catch (error) {
    console.error('Error fetching device details:', error)
  }
}

const closeModal = () => {
  selectedDevice.value = null
}

const markOffline = async (deviceId) => {
  if (!confirm('Are you sure you want to mark this device as offline?')) {
    return
  }
  
  try {
    const response = await fetch(`/zkteko-debug/devices/${deviceId}/mark-offline`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ reason: 'Manually marked offline' })
    })
    
    if (response.ok) {
      await refreshData()
    }
  } catch (error) {
    console.error('Error marking device offline:', error)
  }
}

const clearError = async (deviceId) => {
  try {
    const response = await fetch(`/zkteko-debug/devices/${deviceId}/clear-error`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    if (response.ok) {
      await refreshData()
    }
  } catch (error) {
    console.error('Error clearing device error:', error)
  }
}

const formatTime = (date) => {
  return new Date(date).toLocaleString()
}

onMounted(() => {
  lastUpdated.value = new Date()
  
  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(() => {
    refreshData()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>
