<script setup>
import { ref } from 'vue';

const props = defineProps({
    multiple: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: '.xlsx, .xls, .csv',
    },
    label: {
        type: String,
        default: 'Upload Excel Files',
    },
});

const emit = defineEmits(['change']);

const isDragging = ref(false);
const files = ref([]);

const handleDrop = (e) => {
    isDragging.value = false;
    const droppedFiles = Array.from(e.dataTransfer.files);
    processFiles(droppedFiles);
};

const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    processFiles(selectedFiles);
};

const processFiles = (newFiles) => {
    if (props.multiple) {
        files.value = [...files.value, ...newFiles];
    } else {
        files.value = [newFiles[0]];
    }
    emit('change', files.value);
};

const removeFile = (index) => {
    files.value.splice(index, 1);
    emit('change', files.value);
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-medium leading-6 text-gray-900 mb-2">
            {{ label }}
        </label>
        
        <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="relative flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-xl transition-all duration-200"
            :class="isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-gray-50 hover:bg-gray-100'"
        >
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                <p class="text-xs text-gray-400">{{ accept }} (Max. 10MB)</p>
            </div>
            <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" :multiple="multiple" :accept="accept" @change="handleFileChange" />
        </div>

        <ul v-if="files.length > 0" class="mt-4 divide-y divide-gray-100 rounded-lg border border-gray-100">
            <li v-for="(file, index) in files" :key="index" class="flex items-center justify-between py-3 px-4 bg-white">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900 truncate max-w-[200px]">{{ file.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatSize(file.size) }}</p>
                    </div>
                </div>
                <button @click="removeFile(index)" type="button" class="text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </li>
        </ul>
    </div>
</template>
