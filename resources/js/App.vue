<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const products = ref([]);
const loading = ref(true);
const error = ref('');

const availabilityLabel = {
    in_stock: 'In stock',
    out_of_stock: 'Out of stock',
    preorder: 'Preorder',
};

const getImage = (product) => {
    if (product.image_url) {
        return product.image_url;
    }

    const image = product.images?.[0];

    if (!image) {
        return 'https://placehold.co/640x480?text=No+Image';
    }

    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    }

    return `/storage/${image}`;
};

onMounted(async () => {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.data;
    } catch (e) {
        error.value = 'Unable to load products at the moment.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <main class="mx-auto max-w-7xl px-6 py-10">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Shop Tengai Store</h1>
            <p class="mt-2 text-sm text-gray-600">Products imported from Amazon</p>
        </header>

        <p v-if="loading" class="text-gray-600">Loading products...</p>
        <p v-else-if="error" class="rounded-md bg-red-50 p-4 text-red-700">{{ error }}</p>

        <section v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="product in products"
                :key="product.id"
                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
            >
                <img
                    :src="getImage(product)"
                    :alt="product.title"
                    class="h-48 w-full object-cover"
                >
                <div class="space-y-3 p-4">
                    <p class="text-xs text-gray-500">{{ product.brand || 'Unknown brand' }}</p>
                    <h2 class="line-clamp-2 text-base font-semibold text-gray-900">{{ product.title }}</h2>
                    <p class="text-sm text-gray-600">{{ product.category || 'Uncategorized' }}</p>

                    <div class="flex items-center justify-between">
                        <p class="text-lg font-bold text-amber-700">
                            {{ product.currency || 'USD' }} {{ product.price ?? '-' }}
                        </p>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">
                            {{ availabilityLabel[product.availability] || product.availability || 'Unknown' }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-500">
                        Rating: {{ product.rating ?? 'N/A' }} • {{ product.review_count ?? 0 }} reviews
                    </p>
                </div>
            </article>
        </section>
    </main>
</template>
