<script setup>
import Navbar from "./Navbar.vue";
import Footer from "./Footer.vue";
import FlashMessages from "./FlashMessages.vue";
</script>

<template>
  <div class="flex flex-col min-h-screen">
    <Navbar />

    <main class="grow flex flex-col">
      <div class="pt-[70px] px-4 sm:px-6 lg:px-8 pb-6 max-w-7xl mx-auto w-full flex flex-col grow">
        <FlashMessages />
        <slot />
      </div>
    </main>

    <Footer />
  </div>
</template>
