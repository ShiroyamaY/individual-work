<script setup>

import { onMounted, ref } from 'vue'
import axios from '@/axios'

const totalUsers = ref(0);
const documentsSigned = ref(0);

onMounted(async () => {
  const users = await axios.get('/total-users');
  const docs = await axios.get('/documents-signed');

  totalUsers.value = users.data;
  documentsSigned.value = docs.data;
})


</script>

<template>
  <div class="w-full h-full shrink-0 min-h-full flex-grow flex flex-col justify-evenly items-center">
    <div class="text-center w-fit mx-auto">
      <h1 class="text-8xl tracking-widest ">Welcome</h1>
      <p class="text-6xl tracking-widest ">to</p>
      <strong class="text-8xl tracking-widest color-stale-950">VSign</strong>
    </div>
    <div class="flex gap-8 text-2xl">
      <div><span class="text-green-600 font-bold">{{ documentsSigned }}</span> Documents Signed</div>
      <div><span class="text-green-600 font-bold">{{ totalUsers }}</span> Total Users</div>
    </div>
  </div>
</template>

<style scoped>

</style>
