# Usage examples

## Creating a new page

### 1. Add a controller action

```php
public function actionDashboard(): \yii\web\Response
{
    return $this->inertia('Dashboard', ['stats' => $this->getStats()]);
}
```

### 2. Create the Vue component

Create `resources/js/Pages/Dashboard.vue`:

```vue
<script setup>
import { Head } from "@inertiajs/vue3";

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
});
</script>

<template>
  <Head title="Dashboard" />
  <div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
  </div>
</template>
```

The page automatically uses the `Layout` component configured in `resources/js/app.js`.

## Form submission with validation

Inertia handles form state and server-side validation errors:

```vue
<script setup>
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  "MyModel[name]": "",
  "MyModel[email]": "",
});

const submit = () => {
  form.post("/my-controller/action", { preserveScroll: true });
};
</script>

<template>
  <form @submit.prevent="submit">
    <input v-model="form['MyModel[name]']" type="text" />
    <p v-if="form.errors['name']" class="text-red-500 text-sm">
      {{ form.errors["name"] }}
    </p>
    <button type="submit" :disabled="form.processing">Submit</button>
  </form>
</template>
```

## Accessing shared props

Shared props are defined in `config/web.php` under the `inertia` component. Access them in any Vue component:

```vue
<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth?.user;
const turnstileKey = page.props.turnstileSiteKey;
</script>
```

Available shared props: `auth`, `flash`, `turnstileSiteKey`.

## Using Flowbite Vue components

Import components from `flowbite-vue`:

```vue
<script setup>
import { FwbAlert, FwbBadge, FwbTable } from "flowbite-vue";
</script>

<template>
  <FwbAlert type="success" closable>Operation completed.</FwbAlert>
  <FwbBadge type="green" size="sm">Active</FwbBadge>
</template>
```

## Internal navigation

Use Inertia's `<Link>` component instead of `<a>` for internal navigation:

```vue
<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
  <Link href="/site/about" class="text-primary-600">About</Link>
  <Link href="/user/logout" method="post" as="button">Logout</Link>
</template>
```

## Next steps

- 📚 [Installation Guide](installation.md)
- ⚙️ [Configuration Guide](configuration.md)
- 🧪 [Testing Guide](testing.md)
