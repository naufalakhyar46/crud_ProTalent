<script>
import VueTailwindDatepicker from 'vue-tailwind-datepicker'
import dayjs from 'dayjs'

export default defineComponent({
    components: {
        VueTailwindDatepicker,
    },
    props: ['column', 'endpointData', 'search', 'filtersConfig', 'counter', 'searchBy', 'btnCreate', 'noDataMessage'],
    async setup(props) {
        // global var
        const columns = props.column
        const counter = props.counter
        const data = ref([])
        const originalData = ref([])
        let queryParams = {}
        const noDataMessage = ref(props.noDataMessage)

        // Pagination Logic
        const currentPage = ref()
        const itemsPerPage = ref()
        const itemsPerPageOptions = [10, 20, 50]
        const totalPages = ref()

        const initialize = async () => {
            try {
                const { data: response } = await useSallyFetchCms(props.endpointData, {})
                data.value = response.value.data.records
                originalData.value = data.value
                currentPage.value = response.value.data.current_page
                itemsPerPage.value = response.value.data.page_size
                totalPages.value = response.value.data.max_page
            }
            catch (error) {
                // console.log(error)
            }
        }

        onMounted(() => {
            initialize()
        })

        watch(itemsPerPage, async () => {
            queryParams.page = 1
            queryParams.limit = itemsPerPage.value
            const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                params: queryParams,
            })
            currentPage.value = 1
            data.value = response.value.data.records
            originalData.value = data.value
            totalPages.value = response.value.data.max_page
        })

        const paginationList = computed(() => {
            let startPage = 1
            let endPage = totalPages.value
            const paginationSize = 5
            const halfPaginationSize = Math.floor(paginationSize / 2)
            const pageList = []

            if (totalPages.value > paginationSize) {
                if (currentPage.value <= halfPaginationSize) {
                    endPage = paginationSize
                }
                else if (currentPage.value >= totalPages.value - halfPaginationSize) {
                    startPage = totalPages.value - paginationSize + 1
                }
                else {
                    startPage = currentPage.value - halfPaginationSize
                    endPage = currentPage.value + halfPaginationSize
                }
            }

            for (let i = startPage; i <= endPage; i++)
                pageList.push(i)

            return pageList
        })

        const goToPage = async (pageNumber) => {
            currentPage.value = pageNumber
            queryParams.page = currentPage.value
            const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                params: queryParams,
            })
            data.value = response.value.data.records
            originalData.value = data.value
        }

        const nextPage = async () => {
            currentPage.value += 1
            queryParams.page = currentPage.value
            if (currentPage.value <= totalPages.value) {
                const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                    params: queryParams,
                })
                data.value = response.value.data.records
                originalData.value = data.value
            }
            else {
                currentPage.value = totalPages.value
            }
        }

        const prevPage = async () => {
            currentPage.value -= 1
            queryParams.page = currentPage.value
            if (currentPage.value >= 1) {
                const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                    params: queryParams,
                })
                data.value = response.value.data.records
                originalData.value = data.value
            }
            else {
                currentPage.value = 1
            }
        }
        // end of pagination logic

        // filters logic
        const filters = reactive({})
        const { filtersConfig } = toRefs(props)

        const applyFilters = async (isReset) => {
            currentPage.value = 1
            queryParams.page = 1
            for (const filter of filtersConfig.value) {
                if (filters[filter.column]) {
                    if (filter.type === 'daterange') {
                        queryParams.start_date = dayjs(filters[filter.column][0]).format('YYYY-MM-DD')
                        queryParams.end_date = dayjs(filters[filter.column][1]).format('YYYY-MM-DD')
                    }
                    else {
                        queryParams[filter.column] = filters[filter.column]
                    }
                }
            }

            const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                params: queryParams,
            })

            data.value = response.value.data.records
            originalData.value = data.value
            totalPages.value = response.value.data.max_page

            if (data.value === null && !isReset)
                return noDataMessage.value = 'Hasil pencarian tidak ditemukan.'

            if (isReset)
                return noDataMessage.value = props.noDataMessage
        }
        // end of filters logic

        // search logic
        const search = props.search
        const searchBy = props.searchBy
        const btnCreate = props.btnCreate
        const searchText = ref('')
        const selectedSearchOption = searchBy ? ref(searchBy[0].value) : ref('')
        const counting = reactive({
            searchBy: searchBy ? 2 : 0,
            btnCreate: btnCreate ? 2 : 0,
        })
        let debounceTimeout = null

        const cols = reactive({
            tot: 9 - counting.searchBy - counting.btnCreate,
            span: '',
        })

        if (cols.tot === 5)
            cols.span = 'col-span-5'
        else if (cols.tot === 7)
            cols.span = 'col-span-7'
        else
            cols.span = 'col-span-9'

        const searchData = async () => {
            const preservedQueryParams = { ...queryParams }

            if (searchText.value !== '') {
                const searchByColumn = selectedSearchOption.value
                if (searchByColumn) {
                    queryParams = {
                        page: 1,
                        ...Object.entries(preservedQueryParams)
                            .filter(([key]) => !key.startsWith('search_by_') && key !== 'page' && key !== 'search')
                            .reduce((obj, [key, value]) => ({ ...obj, [key]: value }), {}),
                        [`search_by_${searchByColumn}`]: searchText.value,
                    }
                }
                else {
                    queryParams = {
                        page: 1,
                        ...Object.entries(preservedQueryParams)
                            .filter(([key]) => !key.startsWith('search_by_') && key !== 'page' && key !== 'search')
                            .reduce((obj, [key, value]) => ({ ...obj, [key]: value }), {}),
                        search: searchText.value,
                    }
                }

                const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                    params: queryParams,
                })

                currentPage.value = 1
                data.value = response.value.data.records
                originalData.value = data.value
                totalPages.value = response.value.data.max_page
                noDataMessage.value = 'Hasil pencarian tidak ditemukan.'
            }
            else {
                queryParams = {
                    page: 1,
                    ...Object.entries(preservedQueryParams)
                        .filter(([key]) => !key.startsWith('search_by_') && key !== 'page' && key !== 'search')
                        .reduce((obj, [key, value]) => ({ ...obj, [key]: value }), {}),
                }
                const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                    params: queryParams,
                })

                currentPage.value = 1
                data.value = response.value.data.records
                totalPages.value = response.value.data.max_page
                noDataMessage.value = props.noDataMessage
            }
        }

        watch(searchText, () => {
            clearTimeout(debounceTimeout)
            debounceTimeout = setTimeout(() => {
                searchData()
            }, 350)
        })
        watch(selectedSearchOption, () => {
            searchData()
        })

        // end of search logic

        const resetFilters = () => {
            for (const filter of filtersConfig.value)
                filters[filter.column] = undefined

            const clearBtn = document.querySelectorAll('.k-select .k-input div.relative .input--append svg.iconify--material-symbols')

            for (let i = 0; i < clearBtn.length; i++) {
                const element = clearBtn[i]
                element.dispatchEvent(new Event('click'))
            }

            queryParams = {}
            searchText.value = ''
            itemsPerPage.value = 10
            applyFilters(true)
        }

        // sort logic
        const sortConfig = reactive({
            column: '',
            direction: 'ASC',
        })

        const sortByColumn = async (columnName) => {
            if (sortConfig.column === columnName) {
                sortConfig.direction = sortConfig.direction === 'ASC' ? 'DESC' : 'ASC'
            }
            else {
                sortConfig.column = columnName
                sortConfig.direction = 'ASC'
            }

            queryParams.order_by = sortConfig.column
                ? `${sortConfig.column} ${sortConfig.direction}`
                : ''

            const { data: response } = await useSallyFetchCms(() => props.endpointData, {
                params: queryParams,
            })

            data.value = response.value.data.records
            originalData.value = data.value
            totalPages.value = response.value.data.max_page
        }

        const isSortedColumn = computed(() => {
            return (columnName) => {
                return sortConfig.column === columnName
            }
        })
        // end of sort logic

        return {
            columns,
            counter,
            data,
            currentPage,
            totalPages,
            goToPage,
            itemsPerPage,
            itemsPerPageOptions,
            nextPage,
            prevPage,
            search,
            searchBy,
            cols,
            btnCreate,
            searchText,
            filters,
            filtersConfig,
            applyFilters,
            resetFilters,
            paginationList,
            sortConfig,
            sortByColumn,
            isSortedColumn,
            selectedSearchOption,
            noDataMessage,
        }
    },
})
</script>

<template>
    <div>
        <!-- search bar -->
        <div v-if="search" class="flex grid items-end grid-cols-9 gap-4">
            <div v-if="searchBy" class="col-span-2">
                <label for="searchBy" class="hidden mb-2 text-sm font-medium text-gray-400 md:block">Cari
                    Berdasarkan</label>
                <KSelect id="searchBy" v-model="selectedSearchOption" :items="searchBy" />
            </div>
            <div :class="cols.span" class="relative">
                <KInput id="default-search" v-model="searchText" placeholder="Cari ...">
                    <template #prepend>
                        <Icon name="mdi:magnify" size="20" color="gray" />
                    </template>
                </KInput>
            </div>
            <div v-if="btnCreate" class="col-span-2">
                <button
                    class="w-full bg-blue-500 hover:bg-blue-700 text-white text-sm truncate shrink-0 font-bold px-3 py-2 xl:px-0 py-2.5 rounded"
                    @click="navigateTo(btnCreate.route)">
                    <Icon name="mdi:plus" size="22" />
                    {{ btnCreate.name }}
                </button>
            </div>
        </div>
        <hr class="mt-5">
        <!-- end of search bar -->

        <!-- filter -->
        <div v-if="filtersConfig">
            <div class="flex grid items-end w-full gap-5 mt-3 mb-5 xl:grid-cols-5 lg:grid-cols-4 md:grid-cols-3">
                <template v-for="filter in filtersConfig" :key="filter">
                    <div>
                        <label :for="filter.column" class="block mb-2 text-sm font-medium text-gray-400">{{ filter.label
                            }}</label>
                        <KSelect v-if="filter.type === 'dropdown'" :id="filter.column" v-model="filters[filter.column]"
                            :items="filter.option" searchable :placeholder="filter.placeholder" />
                        <KSelect v-if="filter.type === 'multiple-dropdown'" :id="filter.column"
                            v-model="filters[filter.column]" :items="filter.option" mode="multiple" :clearable="true"
                            :placeholder="filter.placeholder" :style="{
            whiteSpace: 'pre',
            overflowWrap: 'anywhere',
            wordWrap: 'anywhere',
            textOverlow: 'ellipsis',
        }" />
                        <input v-else-if="filter.type === 'datepicker'" :id="filter.column"
                            v-model="filters[filter.column]" type="date"
                            class="bg-white focus:bg-gray-50 border border-gray-300 text-gray-400 text-sm rounded focus:ring-blue-500 focus:border-blue-500 block p-2.5 w-full">
                        <slot v-else-if="filter.type === 'custom'" :name="`custFilter-${filter.column}`" />
                        <span v-else-if="filter.type === 'daterange'" id="date-custom-range" class="w-full">
                            <VueTailwindDatepicker v-model="filters[filter.column]"
                                input-classes="custom-style-date-default bg-white h-[42.3px] focus:bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded block p-2.5 w-full"
                                :placeholder="filter.placeholder" :value="filters[filter.column] && filters[filter.column].length === 2
            ? `${filters[filter.column][0]} - ${filters[filter.column][1]}`
            : ''" separator=" - " :formatter="{ date: 'YYYY-MM-DD', month: 'MMM' }" :options="{
            shortcuts: {
                today: 'Hari ini',
                yesterday: 'Kemarin',
                past: period => `${period} hari terakhir`,
                currentMonth: 'Bulan ini',
                pastMonth: 'Bulan lalu',
            },
            footer: {
                apply: 'Terapkan',
                cancel: 'Batal',
            },
        }" />
                        </span>
                    </div>
                </template>
                <div class="flex w-full">
                    <button
                        class="px-3 py-3 text-sm font-bold text-white bg-blue-500 rounded xl:w-1/2 hover:bg-blue-700 shrink-0 xl:px-0"
                        @click="applyFilters(false)">
                        Terapkan
                    </button>
                    <button
                        class="px-3 py-3 ml-3 text-sm font-bold text-gray-700 rounded xl:w-1/2 hover:bg-gray-300 shrink-0 xl:px-0"
                        @click="resetFilters">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
        <!-- end of filter -->

        <!-- table -->
        <div class="w-full overflow-x-auto">
            <table class="mt-6 text-sm text-left text-gray-500">
                <thead class="text-md capitalize border-b text-[#8B8B8B]">
                    <tr>
                        <th v-if="counter" scope="col" class="px-6 py-5">
                            No
                        </th>
                        <th v-for="index in columns" :key="index" scope="col" class="px-6 py-5 hover:bg-gray-100"
                            :class="{ 'cursor-pointer': index.sortable }"
                            @click="index.sortable ? sortByColumn(index.field) : null">
                            <div class="flex items-center gap-2">
                                {{ index.label }}
                                <span v-if="index.sortable">
                                    <Icon name="lucide:chevrons-up-down"
                                        :color="isSortedColumn(index.field) ? 'blue' : 'gray'" />
                                </span>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-5">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="data && data.length > 0">
                        <tr v-for="(row, i) in data" :key="i"
                            class="bg-white border-b hover:bg-[#ECF6FF] hover:text-[#4782FC] text-[14px]">
                            <td v-if="counter" class="px-6 py-4">
                                {{ startIndex + i }}
                            </td>
                            <td v-for="col in column" :key="col" class="px-6 py-4">
                                <template v-if="col.isCustom">
                                    <slot :name="col.field" :row="row" :cust-column="col" />
                                </template>
                                <template v-else>
                                    {{ row[col.field] }}
                                </template>
                            </td>
                            <td class="flex items-center px-6 py-4 space-x-3">
                                <slot name="customActions" :row="row" />
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td class="py-5 text-center hover:bg-white" :colspan="columns.length + 1">
                            {{ noDataMessage }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- end of table -->

        <!-- pagination -->
        <nav v-if="data && data.length > 0" class="flex items-center justify-between pt-4"
            aria-label="Table navigation">
            <span class="flex gap-3 text-sm font-normal text-gray-500 dark:text-gray-400">
                Perlihatkan
                <select v-model="itemsPerPage"
                    class="block p-0 pl-2 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option v-for="option in itemsPerPageOptions" :key="option" :value="option">{{ option }}</option>
                </select>
                baris
            </span>
            <ul v-if="totalPages > 1" class="inline-flex h-8 -space-x-px text-sm">
                <li>
                    <a href="#"
                        class="flex items-center justify-center h-10 p-4 mr-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                        aria-label="previous-pagination" @click="prevPage()" @click.prevent>
                        <Icon name="mdi:menu-left-outline" />
                    </a>
                </li>
                <li v-for="pageNumber in paginationList" :key="pageNumber">
                    <a href="#"
                        class="flex items-center justify-center h-10 p-4 leading-tight text-gray-500 bg-white border border-0 border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                        :class="{
            'active': pageNumber === currentPage,
            'rounded-l-lg': pageNumber === 1,
            'rounded-r-lg': pageNumber === totalPages,
        }" @click="goToPage(pageNumber)" @click.prevent>
                        {{ pageNumber }}
                    </a>
                </li>
                <li>
                    <a href="#"
                        class="flex items-center justify-center h-10 p-4 ml-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                        aria-label="next-pagination" @click="nextPage()" @click.prevent>
                        <Icon name="mdi:menu-right-outline" />
                    </a>
                </li>
            </ul>
        </nav>
        <!-- end of pagination -->
    </div>
</template>
