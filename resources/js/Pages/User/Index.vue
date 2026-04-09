<script setup>
import { ref, watch, onUnmounted } from "vue";
import { Head, router } from "@inertiajs/vue3";
import {
  FwbTable,
  FwbTableHead,
  FwbTableBody,
  FwbTableRow,
  FwbTableCell,
  FwbTableHeadCell,
  FwbBadge,
  FwbPagination,
} from "flowbite-vue";

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  pagination: {
    type: Object,
    default: () => ({
      totalCount: 0,
      pageSize: 10,
      currentPage: 1,
      pageCount: 1,
    }),
  },
  sort: {
    type: Object,
    default: () => ({ attributes: {} }),
  },
  filters: {
    type: Object,
    default: () => ({ username: "", email: "", status: "" }),
  },
});

const STATUS_ACTIVE = 10;
const STATUS_INACTIVE = 9;
const STATUS_DELETED = 0;

const statusMap = {
  [STATUS_ACTIVE]: { label: "Active", type: "green" },
  [STATUS_INACTIVE]: { label: "Inactive", type: "yellow" },
  [STATUS_DELETED]: { label: "Deleted", type: "red" },
};

const filterUsername = ref(props.filters.username || "");
const filterEmail = ref(props.filters.email || "");
const filterStatus = ref(props.filters.status ?? "");
let debounceTimer = null;

const getSortParam = (attributes = {}) => {
  const [entry] = Object.entries(attributes);
  return entry ? (entry[1] === 3 ? `-${entry[0]}` : entry[0]) : "";
};

const sortParam = ref(getSortParam(props.sort.attributes));

watch(
  () => props.sort.attributes,
  (attributes) => {
    sortParam.value = getSortParam(attributes);
  },
  { deep: true },
);

const buildParams = () => {
  const params = {};
  if (filterUsername.value) params["UserSearch[username]"] = filterUsername.value;
  if (filterEmail.value) params["UserSearch[email]"] = filterEmail.value;
  if (filterStatus.value !== "") params["UserSearch[status]"] = filterStatus.value;

  if (sortParam.value) params.sort = sortParam.value;

  return params;
};

const cancelPendingFilter = () => {
  clearTimeout(debounceTimer);
  debounceTimer = null;
};

const applyFilters = () => {
  cancelPendingFilter();
  debounceTimer = setTimeout(() => {
    router.get("/user/index", buildParams(), {
      preserveState: true,
      preserveScroll: true,
    });
    debounceTimer = null;
  }, 300);
};

watch([filterUsername, filterEmail, filterStatus], applyFilters);
onUnmounted(cancelPendingFilter);

const sortBy = (attribute) => {
  cancelPendingFilter();
  const params = buildParams();
  params.sort = sortParam.value === attribute ? `-${attribute}` : attribute;
  sortParam.value = params.sort;
  router.get("/user/index", params, {
    preserveState: true,
    preserveScroll: true,
  });
};

const goToPage = (page) => {
  if (page < 1 || page > props.pagination.pageCount || page === props.pagination.currentPage) {
    return;
  }

  cancelPendingFilter();
  router.get("/user/index", { ...buildParams(), page }, { preserveState: true, preserveScroll: true });
};

const sortIcon = (attribute) => {
  const order = props.sort.attributes[attribute];
  if (order === 4) return " \u25B2";
  if (order === 3) return " \u25BC";
  return "";
};

const ariaSort = (attribute) => {
  const order = props.sort.attributes[attribute];
  if (order === 4) return "ascending";
  if (order === 3) return "descending";
  return "none";
};

const formatDate = (timestamp) => {
  if (!timestamp) return "";
  const d = new Date(timestamp * 1000);
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
};

const getStatus = (status) => statusMap[status] || { label: "Unknown", type: "dark" };
</script>

<template>
  <Head title="Users" />

  <div class="grow flex items-center justify-center">
    <div
      class="overflow-hidden rounded-2xl shadow-lg dark:shadow-gray-900/50 bg-gray-50 dark:bg-gray-800 w-full max-w-[1000px]"
    >
      <div class="flex flex-col md:flex-row">
        <!-- Brand panel -->
        <div class="hidden md:flex md:w-1/3 login-brand-panel text-white">
          <div class="flex flex-col justify-between p-5 lg:p-6 w-full">
            <div>
              <img src="/images/yii3_full_white_for_dark.svg" alt="Yii Framework" class="mb-6" height="36" />
            </div>
            <div>
              <h1 class="font-display font-bold mb-3 text-[1.75rem] leading-tight">User<br />Directory</h1>
              <p class="opacity-75 text-[0.9rem]">
                Browse, filter, and sort registered users. Use the search fields to find specific accounts.
              </p>
            </div>
            <div class="mt-4">
              <span class="inline-block bg-white/20 rounded-full px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                {{ pagination.totalCount }}
                {{ pagination.totalCount === 1 ? "user" : "users" }}
              </span>
            </div>
          </div>
        </div>

        <!-- Table panel -->
        <div class="w-full md:w-2/3">
          <div class="p-4 lg:p-5">
            <!-- Mobile header -->
            <div class="md:hidden text-center mb-4">
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">Users</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Browse and filter registered users</p>
            </div>

            <div class="overflow-x-auto">
              <FwbTable hoverable>
                <FwbTableHead>
                  <FwbTableHeadCell :aria-sort="ariaSort('username')">
                    <button
                      type="button"
                      class="cursor-pointer bg-transparent border-0 p-0 hover:text-gray-900 dark:hover:text-white"
                      @click.prevent="sortBy('username')"
                    >
                      Username{{ sortIcon("username") }}
                    </button>
                  </FwbTableHeadCell>
                  <FwbTableHeadCell :aria-sort="ariaSort('email')">
                    <button
                      type="button"
                      class="cursor-pointer bg-transparent border-0 p-0 hover:text-gray-900 dark:hover:text-white"
                      @click.prevent="sortBy('email')"
                    >
                      Email{{ sortIcon("email") }}
                    </button>
                  </FwbTableHeadCell>
                  <FwbTableHeadCell :aria-sort="ariaSort('status')">
                    <button
                      type="button"
                      class="cursor-pointer bg-transparent border-0 p-0 hover:text-gray-900 dark:hover:text-white"
                      @click.prevent="sortBy('status')"
                    >
                      Status{{ sortIcon("status") }}
                    </button>
                  </FwbTableHeadCell>
                  <FwbTableHeadCell :aria-sort="ariaSort('created_at')">
                    <button
                      type="button"
                      class="cursor-pointer bg-transparent border-0 p-0 hover:text-gray-900 dark:hover:text-white"
                      @click.prevent="sortBy('created_at')"
                    >
                      Joined{{ sortIcon("created_at") }}
                    </button>
                  </FwbTableHeadCell>
                </FwbTableHead>

                <!-- Filter row -->
                <FwbTableBody>
                  <FwbTableRow>
                    <FwbTableCell class="!py-2 bg-gray-100 dark:bg-gray-900/50">
                      <input
                        v-model="filterUsername"
                        type="text"
                        aria-label="Filter users by username"
                        class="w-full text-xs px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500/25 outline-none"
                        placeholder="Filter..."
                      />
                    </FwbTableCell>
                    <FwbTableCell class="!py-2 bg-gray-100 dark:bg-gray-900/50">
                      <input
                        v-model="filterEmail"
                        type="text"
                        aria-label="Filter users by email"
                        class="w-full text-xs px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500/25 outline-none"
                        placeholder="Filter..."
                      />
                    </FwbTableCell>
                    <FwbTableCell class="!py-2 bg-gray-100 dark:bg-gray-900/50">
                      <select
                        v-model="filterStatus"
                        aria-label="Filter users by status"
                        class="w-full text-xs px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500/25 outline-none"
                      >
                        <option value="">All</option>
                        <option :value="STATUS_ACTIVE">Active</option>
                        <option :value="STATUS_INACTIVE">Inactive</option>
                        <option :value="STATUS_DELETED">Deleted</option>
                      </select>
                    </FwbTableCell>
                    <FwbTableCell class="!py-2 bg-gray-100 dark:bg-gray-900/50" />
                  </FwbTableRow>
                </FwbTableBody>

                <!-- Data rows -->
                <FwbTableBody>
                  <FwbTableRow v-if="users.length === 0">
                    <FwbTableCell colspan="4" class="text-center !py-10 text-gray-500 dark:text-gray-400">
                      No results found.
                    </FwbTableCell>
                  </FwbTableRow>

                  <FwbTableRow v-for="user in users" :key="user.id">
                    <FwbTableCell class="font-medium text-gray-900 dark:text-white">
                      {{ user.username }}
                    </FwbTableCell>
                    <FwbTableCell>
                      <a
                        :href="`mailto:${user.email}`"
                        class="text-primary-600 dark:text-primary-400 hover:underline"
                        >{{ user.email }}</a
                      >
                    </FwbTableCell>
                    <FwbTableCell>
                      <FwbBadge :type="getStatus(user.status).type" size="sm">
                        {{ getStatus(user.status).label }}
                      </FwbBadge>
                    </FwbTableCell>
                    <FwbTableCell class="whitespace-nowrap">
                      {{ formatDate(user.created_at) }}
                    </FwbTableCell>
                  </FwbTableRow>
                </FwbTableBody>
              </FwbTable>

              <!-- Summary -->
              <div v-if="users.length > 0" class="text-xs text-gray-500 dark:text-gray-400 text-right mt-2">
                Showing
                {{ (pagination.currentPage - 1) * pagination.pageSize + 1 }}-{{
                  Math.min(pagination.currentPage * pagination.pageSize, pagination.totalCount)
                }}
                of {{ pagination.totalCount }} {{ pagination.totalCount === 1 ? "item" : "items" }}.
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.pageCount > 1" class="flex justify-center mt-4">
              <FwbPagination
                :model-value="pagination.currentPage"
                :total-pages="pagination.pageCount"
                :show-labels="false"
                previous-label="Prev"
                next-label="Next"
                @page-changed="goToPage"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
