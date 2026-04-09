<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
  status: {
    type: Number,
    default: 500,
  },
  message: {
    type: String,
    default: "An internal server error occurred.",
  },
});
</script>

<template>
  <Head :title="String(status)" />

  <div class="grow flex items-center justify-center text-center">
    <div class="mx-auto max-w-sm sm:max-w-md md:max-w-lg">
      <h1
        class="font-display text-8xl font-bold text-gray-200 dark:text-gray-700 mb-2"
      >
        {{ status }}
      </h1>
      <h2
        class="text-2xl sm:text-3xl font-semibold mb-4 text-gray-900 dark:text-white"
      >
        {{ status >= 500 ? "An internal server error occurred." : message }}
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
        The above error occurred while the Web server was processing your
        request. Please contact us if you think this is a server error. Thank
        you.
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
