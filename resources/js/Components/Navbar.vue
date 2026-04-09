<script setup>
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ThemeToggle from "./ThemeToggle.vue";

const page = usePage();
const open = ref(false);

const isActive = (href) => {
  const current = page.url;
  return current === href || current.startsWith(href + "?") || current.startsWith(href + "/");
};
</script>

<template>
  <header>
    <nav class="fixed top-0 inset-x-0 z-50 bg-gray-950 border-b border-gray-800/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Brand -->
          <Link
            href="/"
            class="font-display font-bold text-lg text-white tracking-tight hover:text-primary-400 transition-colors"
          >
            {{ page.props.appName }}
          </Link>

          <!-- Desktop nav -->
          <div class="hidden md:flex items-center gap-1">
            <Link
              href="/"
              prefetch
              :class="[
                'px-3 py-2 text-sm rounded-md transition-colors',
                isActive('/') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
              ]"
            >
              Home
            </Link>
            <Link
              href="/site/about"
              prefetch
              :class="[
                'px-3 py-2 text-sm rounded-md transition-colors',
                isActive('/site/about') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
              ]"
            >
              About
            </Link>
            <Link
              href="/site/contact"
              prefetch
              :class="[
                'px-3 py-2 text-sm rounded-md transition-colors',
                isActive('/site/contact') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
              ]"
            >
              Contact
            </Link>
            <Link
              v-if="page.props.auth.canViewUsers"
              href="/user/index"
              prefetch
              :class="[
                'px-3 py-2 text-sm rounded-md transition-colors',
                isActive('/user/index') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
              ]"
            >
              Users
            </Link>
            <Link
              v-if="page.props.auth.isGuest"
              href="/user/signup"
              prefetch
              :class="[
                'px-3 py-2 text-sm rounded-md transition-colors',
                isActive('/user/signup') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
              ]"
            >
              Signup
            </Link>
            <Link
              v-if="page.props.auth.isGuest"
              href="/user/login"
              prefetch
              :class="[
                'px-3 py-2 text-sm rounded-md transition-colors',
                isActive('/user/login') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
              ]"
            >
              Login
            </Link>
            <Link
              v-if="!page.props.auth.isGuest"
              href="/user/logout"
              method="post"
              as="button"
              class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-md transition-colors cursor-pointer"
            >
              Logout ({{ page.props.auth.user?.username }})
            </Link>
            <div class="ml-2 pl-2 border-l border-gray-800">
              <ThemeToggle />
            </div>
          </div>

          <!-- Mobile toggle -->
          <div class="flex items-center gap-2 md:hidden">
            <ThemeToggle />
            <button
              class="p-2 text-gray-400 hover:text-white rounded-md transition-colors"
              :aria-expanded="open"
              aria-label="Toggle navigation"
              @click="open = !open"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path v-if="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-show="open" class="md:hidden border-t border-gray-800/50 bg-gray-950">
        <div class="px-4 py-3 space-y-1">
          <Link
            href="/"
            :class="[
              'block px-3 py-2 text-sm rounded-md',
              isActive('/') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
            ]"
          >
            Home
          </Link>
          <Link
            href="/site/about"
            :class="[
              'block px-3 py-2 text-sm rounded-md',
              isActive('/site/about') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
            ]"
          >
            About
          </Link>
          <Link
            href="/site/contact"
            :class="[
              'block px-3 py-2 text-sm rounded-md',
              isActive('/site/contact') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
            ]"
          >
            Contact
          </Link>
          <Link
            v-if="page.props.auth.canViewUsers"
            href="/user/index"
            :class="[
              'block px-3 py-2 text-sm rounded-md',
              isActive('/user/index') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
            ]"
          >
            Users
          </Link>
          <Link
            v-if="page.props.auth.isGuest"
            href="/user/signup"
            :class="[
              'block px-3 py-2 text-sm rounded-md',
              isActive('/user/signup') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
            ]"
          >
            Signup
          </Link>
          <Link
            v-if="page.props.auth.isGuest"
            href="/user/login"
            :class="[
              'block px-3 py-2 text-sm rounded-md',
              isActive('/user/login') ? 'text-primary-400 font-medium' : 'text-gray-400 hover:text-white',
            ]"
          >
            Login
          </Link>
          <Link
            v-if="!page.props.auth.isGuest"
            href="/user/logout"
            method="post"
            as="button"
            class="block px-3 py-2 text-sm text-gray-400 hover:text-white rounded-md text-left w-full cursor-pointer"
          >
            Logout ({{ page.props.auth.user?.username }})
          </Link>
        </div>
      </div>
    </nav>
  </header>
</template>
