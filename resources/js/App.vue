<template>
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl m-8">
        <div class="md:flex">
            <div class="p-8 text-center">
                <h1 class="text-2xl font-bold mb-4 text-indigo-600">Click Counter App</h1>
                <p class="text-lg mb-6">You have clicked the button:</p>
                <div class="text-4xl font-mono mb-6 text-blue-700">{{ counter }}</div>
                <button
                    @click="incrementCounter"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded transition duration-300"
                >
                    Click Me!
                </button>
                <div class="mt-6">
                    <button
                        @click="resetCounter"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'

export default {
    name: 'ClickCounterApp',
    setup() {
        const counter = ref(0)

        const incrementCounter = () => {
            counter.value++
        }

        const resetCounter = () => {
            counter.value = 0
        }

        return {
            counter,
            incrementCounter,
            resetCounter
        }
    }
}
</script>

<style scoped>
/* Additional component-specific styles can go here */
</style>
