<script setup>
import { onMounted, ref } from 'vue';
import { useDocumentsStore } from '@/store/documents';
import { formatDate } from '@/utils/dateUtils';
import debounce from '@/utils/debounce.js'

const { documents, isLoading, errorMessage, fetchDocuments, signDocument } = useDocumentsStore();

onMounted(() => {
  fetchDocuments();
});

const documentSignResponse = ref(null);

const handleSignDocument = async (documentId) => {
  const result = await signDocument(documentId);
  documentSignResponse.value = { message: result.message };
};

const debounceFetchDocuments = debounce(fetchDocuments, 500);

const filterInput = (event) => {
  const input = event.target.value;

  debounceFetchDocuments(input);

}

</script>

<template>
  <div class="justify-self-start">
    <input class="h-12 max-w-2xl w-full px-8 bg-white rounded-lg shadow-lg my-4" type="text" placeholder="Find" @input="filterInput">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto flex flex-col py-4">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Documents for Signature</h1>

      <p v-if="errorMessage" class="text-red-600">{{ errorMessage }}</p>

      <p v-if="isLoading" class="text-gray-600">Loading documents...</p>

      <div v-if="documentSignResponse" class="p-4 mb-4 bg-green-100 border-l-4 border-green-500 text-green-800">
        <p>{{ documentSignResponse.message }}</p>
      </div>

      <p v-if="!isLoading && documents.length === 0" class="text-gray-600 text-center">No documents available for signature.</p>

      <ul v-if="documents.length > 0" class="space-y-4 flex flex-col flex-grow">
        <li
          v-for="document in documents"
          :key="document.id"
          class="p-4 bg-gray-50 rounded-md shadow-sm flex justify-between items-center gap-6"
        >
          <div>
            <h2 class="text-lg font-semibold text-gray-800">{{ document.title }}</h2>
            <p class="text-sm text-gray-600">Added: {{ formatDate(document.createdAt) }}</p>
          </div>
          <button
            @click="handleSignDocument(document.id)"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 cursor-pointer"
          >
            Sign
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>
