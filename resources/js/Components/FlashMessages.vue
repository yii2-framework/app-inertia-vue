<script setup>
import { ref, watch, TransitionGroup } from "vue";
import { usePage } from "@inertiajs/vue3";
import { FwbAlert } from "flowbite-vue";

const page = usePage();
const alerts = ref([]);

const typeMap = {
    success: "success",
    error: "danger",
    danger: "danger",
    info: "info",
    warning: "warning",
};

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash || typeof flash !== "object") {
            alerts.value = [];
            return;
        }

        alerts.value = Object.entries(flash)
            .filter(([, message]) => message)
            .map(([type, message]) => ({
                id: crypto.randomUUID(),
                type: typeMap[type] || "info",
                message,
            }));
    },
    { immediate: true },
);

const dismiss = (id) => {
    alerts.value = alerts.value.filter((a) => a.id !== id);
};
</script>

<template>
    <TransitionGroup name="flash" tag="div">
        <div v-for="alert in alerts" :key="alert.id" class="mb-4">
            <FwbAlert :type="alert.type" closable @close="dismiss(alert.id)">
                {{ alert.message }}
            </FwbAlert>
        </div>
    </TransitionGroup>
</template>

<style scoped>
.flash-enter-active,
.flash-leave-active {
    transition: all 0.3s ease;
}

.flash-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.flash-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
