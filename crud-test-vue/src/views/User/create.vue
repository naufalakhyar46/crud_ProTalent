<script setup>
import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss'
import { FwbInput } from 'flowbite-vue'
import { ref } from 'vue'
import ApiService from "@/composables/ApiService";
import { useRouter } from "vue-router";

const model = ref({})
const refs = ref({})

const router = useRouter();

const saveData = async () => {
    let formData = new FormData(refs.value)
    formData.append('image', model.value.image)
    ApiService.post('/user/store', formData)
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
                router.push('/')
            })
        })
        .catch((err) => {
            if (err.response.data.status == 'error') {
                alert('Error')
            }
        })
}

const upload = (e) => {
    let files = e.target.files[0]
    model.value.image = files
    model.value.path = URL.createObjectURL(files)
}
</script>
<template>

    <div style="margin-top:50px" class="max-w-xl mx-auto">
        <router-link to="/"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Kembali</router-link>
        <h2 class="text-4xl font-extrabold mb-6 mt-3">Tambah User</h2>
        <form ref="refs" @submit.prevent="saveData">
            <div class="w-full mb-5 group">
                <fwb-input v-model="model.name" name="name" label="Name" placeholder="enter your name" size="sm"
                    required />
            </div>
            <div class="w-full mb-5 group">
                <fwb-input v-model="model.email" name="email" type="email" label="Email" placeholder="enter your email"
                    size="sm" required />
            </div>
            <div class="w-full mb-5 group">
                <fwb-input v-model="model.password" name="password" type="password" label="Password"
                    placeholder="enter your password" size="sm" required />
            </div>
            <div class="w-full mb-5 group">
                <img v-if="model.path" :src="model.path" width="40%">
                <fwb-input type="file" label="Image" size="sm" @change="upload" required />
            </div>
            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Submit</button>
        </form>
    </div>

</template>