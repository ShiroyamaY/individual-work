<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

</script>

<template>
  <main class="flex flex-grow flex-col items-center bg-gray-200" :class="{'justify-center': route.name !== 'DocumentsToSign' }">
    <RouterView/>
  </main>
</template>
