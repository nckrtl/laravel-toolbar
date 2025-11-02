<script setup>
import { ref } from 'vue'

const open = ref(false);

 addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && event.metaKey && event.altKey ) {
      open.value = !open.value
    }
  })

  addEventListener('keyup', (event) => {
    if (open.value && event.key === 'Escape') {
      open.value = false
    }
  })

const startHorizon = () => {
  fetch('/_toolbar/horizon/start', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      'Accept': 'application/json',
    },
  })
}

const stopHorizon = () => {
  fetch('/_toolbar/horizon/stop', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      'Accept': 'application/json',
    },
  })
}
</script>

<template>
  <div class="fixed top-0 left-0 w-full h-full flex justify-center items-center text-sm text-white" v-if="open">
    <div class="bg-[#171717]/95 backdrop-blur-sm rounded-2xl max-w-lg w-full border border-white/5">
      <div class="flex flex-col divide-y divide-white/5">
        <input type="text" class="w-full p-4 px-3 focus:outline-none text-white" placeholder="Enter a command" />
        <div class="flex flex-col py-1 px-1 space-y-1">
          <div class="flex justify-between items-center px-2 py-2 rounded-xl  hover:bg-white/5 cursor-pointer" >
            <div class="flex items-center gap-2">
              <div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" class="text-white/50 size-3.5">
                  <path  d="M5.26176342 26.4094389C2.04147988 23.6582233 0 19.5675182 0 15c0-4.1421356 1.67893219-7.89213562 4.39339828-10.60660172C7.10786438 1.67893219 10.8578644 0 15 0c8.2842712 0 15 6.71572875 15 15 0 8.2842712-6.7157288 15-15 15-3.716753 0-7.11777662-1.3517984-9.73823658-3.5905611zM4.03811305 15.9222506C5.70084247 14.4569342 6.87195416 12.5 10 12.5c5 0 5 5 10 5 3.1280454 0 4.2991572-1.9569336 5.961887-3.4222502C25.4934253 8.43417206 20.7645408 4 15 4 8.92486775 4 4 8.92486775 4 15c0 .3105915.01287248.6181765.03811305.9222506z" fill="currentColor">
                  </path>
                </svg>
              </div>
              <div>
                <a href="/horizon" @click="open = false">Open Horizon</a></div>
            </div>
            <span class="opacity-50">Horizon</span>
          </div>
          <div class="flex justify-between items-center px-2 py-2 rounded-xl hover:bg-white/5 cursor-pointer" @click="startHorizon">
            <div class="flex items-center gap-2">
              <div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" class="text-white/50 size-3.5">
                  <path  d="M5.26176342 26.4094389C2.04147988 23.6582233 0 19.5675182 0 15c0-4.1421356 1.67893219-7.89213562 4.39339828-10.60660172C7.10786438 1.67893219 10.8578644 0 15 0c8.2842712 0 15 6.71572875 15 15 0 8.2842712-6.7157288 15-15 15-3.716753 0-7.11777662-1.3517984-9.73823658-3.5905611zM4.03811305 15.9222506C5.70084247 14.4569342 6.87195416 12.5 10 12.5c5 0 5 5 10 5 3.1280454 0 4.2991572-1.9569336 5.961887-3.4222502C25.4934253 8.43417206 20.7645408 4 15 4 8.92486775 4 4 8.92486775 4 15c0 .3105915.01287248.6181765.03811305.9222506z" fill="currentColor">
                  </path>
                </svg>
              </div>
              <div>Start Horizon</div>
            </div>
            <span class="opacity-50">Horizon</span>
          </div>
          <div class="flex justify-between items-center px-2 py-2 rounded-xl hover:bg-white/5 cursor-pointer" @click="stopHorizon">
            <div class="flex items-center gap-2">
              <div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" class="text-white/50 size-3.5">
                  <path  d="M5.26176342 26.4094389C2.04147988 23.6582233 0 19.5675182 0 15c0-4.1421356 1.67893219-7.89213562 4.39339828-10.60660172C7.10786438 1.67893219 10.8578644 0 15 0c8.2842712 0 15 6.71572875 15 15 0 8.2842712-6.7157288 15-15 15-3.716753 0-7.11777662-1.3517984-9.73823658-3.5905611zM4.03811305 15.9222506C5.70084247 14.4569342 6.87195416 12.5 10 12.5c5 0 5 5 10 5 3.1280454 0 4.2991572-1.9569336 5.961887-3.4222502C25.4934253 8.43417206 20.7645408 4 15 4 8.92486775 4 4 8.92486775 4 15c0 .3105915.01287248.6181765.03811305.9222506z" fill="currentColor">
                  </path>
                </svg>
              </div>
              <div>Stop Horizon</div>
            </div>
            <span class="opacity-50">Horizon</span>
          </div>
        </div>
      </div>
      <!-- <button @click="startHorizon">start horizon</button>
      aaa
      <button @click="statusHorizon">status horizon</button> -->
    </div>
  </div>
</template>