<script setup>
import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss'
import { FwbInput } from 'flowbite-vue'
import { ref } from 'vue'
import ApiService from "@/composables/ApiService";
import { useRouter } from "vue-router";

const modelData = ref({})
const refs = ref({})

const router = useRouter();

const getData = async () => {
    ApiService.get("/user")
        .then((res) => {
            let data = res.data.data;
            modelData.value = data
        })
}

getData()

const hapus = (id) => {
    ApiService.delete("/user/destroy/"+id)
        .then((res) => {
            Swal.fire({
                title: res.data.message,
                icon: 'success',
                buttonsStyling: false,
                confirmButtonText: 'Tutup',
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: {
                    actions: 'grid gap-6 md:grid-cols-1',
                    confirmButton:
                        'w-full text-white rounded font-medium text-md px-5 py-2.5 mr-2 bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300',
                },
            }).then(() => {
                getData()
            })
        })
}
</script>
<template>

    <div style="margin-top:50px;width: 70%;" class="mx-auto">
        <router-link to="/user/create"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Tambah</router-link>
        <h2 class="text-4xl font-extrabold mb-6 mt-3">List User</h2>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Image
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Created
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody v-if="modelData.length > 0">
                    <tr v-for="(dataqu, index) in modelData" :key="index" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <a v-if="dataqu.path_image" :href="dataqu.path_image" target="_blank">
                                <img :src="dataqu.path_image" class="rounded-full" style="width:90px;height:90px">
                            </a>
                        </th>
                        <td class="px-6 py-4">
                            {{dataqu.name}}
                        </td>
                        <td class="px-6 py-4">
                            {{dataqu.email}}
                        </td>
                        <td class="px-6 py-4">
                            {{dataqu.created_at}}
                        </td>
                        <td class="px-6 py-4">
                            <router-link :to="'/user/edit/'+dataqu.id"
            class="text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none mr-2">Edit</router-link>
                            <button @click.prevent="hapus(dataqu.id)"
            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Hapus</button>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="5" class="text-lg font-bold text-center">Data Kosong</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</template>