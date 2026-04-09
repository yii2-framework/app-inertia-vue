<script setup>
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
  <Head title="About" />

  <div class="grow flex items-center justify-center text-center">
    <div class="mx-auto max-w-sm sm:max-w-md md:max-w-lg lg:max-w-xl">
      <h1 class="font-display text-3xl sm:text-4xl font-semibold mb-4 text-gray-900 dark:text-white tracking-tight">
        This is the About page.
      </h1>

      <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
        You may modify the following file to customize its content:
        <code class="block mt-2 text-sm font-display text-primary-600 dark:text-primary-400"
          >resources/js/Pages/Site/About.vue</code
        >
      </p>

      <Link
        href="/"
        class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-3 rounded-lg transition-colors"
      >
        Go to Homepage
      </Link>
    </div>
  </div>
</template>
