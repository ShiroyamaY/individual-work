<script setup>
import Navbar from "@/components/Navbar.vue";
</script>
<template>
  <header class="bg-white shadow-md p-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
      <router-link to="/">
        <h1 class="text-2xl font-bold text-gray-800">VSign</h1>
      </router-link>
      <Navbar/>
    </div>
  </header>
</template>
