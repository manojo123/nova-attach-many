<template>
    <default-field :field="field" :full-width-content="field.fullWidth" :show-help-text="false">
        <template slot="field" :class="{'border-danger border': hasErrors}">
            <div :class="{'border-danger border': hasErrors}">
                <div v-if="field.showToolbar" class="flex border-b-0 border border-40 relative">
                    <div v-if="preview" class="flex justify-center items-center absolute pin z-10 bg-white">
                        <h3>{{ __('Selected Items') }} ({{ selected.length  }})</h3>
                    </div>

                    <div @click="selectAll" class="w-16 text-center flex justify-center items-center">
                        <fake-checkbox :checked="selectingAll" class="cursor-pointer"></fake-checkbox>
                    </div>

                    <div class="flex-1 flex items-center relative">
                        <input @keypress.enter.prevent="onEnter" v-model="search" type="text" :placeholder="__('Search')" class="form-control form-input form-input-bordered w-full ml-0 m-4">
                        <span v-if="search" @click="clearSearch" class="pin-r font-sans font-bolder absolute pr-8 cursor-pointer text-black hover:text-80">x</span>
                    </div>
                </div>

                <div class="border border-40 relative overflow-auto" :style="{ height: field.height }">
                    <div v-if="loading" class="flex justify-center items-center absolute pin z-50 bg-white">
                        <loader class="text-60" />
                    </div>
                    <div class="w-full mx-auto">
                        <div class="bg-white shadow-md rounded mb-3">
                            <table class="text-left w-full border-collapse border-60" v-if="resources.length">
                                <thead>
                                    <tr>
                                        <th class="w-16 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light text-80 border-60"></th>
                                        <th class="py-2 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light text-80 border-60">{{ __('ITEMS') }}</th>
                                        <th 
                                        v-for="pivot in resources[0].pivots" 
                                        class="w-32 py-2 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light text-80 border-60">
                                            {{ pivot.display }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="hover:bg-grey-lighter" v-for="resource in resources" :key="resource.value">
                                        <td class="border-b border-grey-light text-80 border-60">
                                            <div class="flex justify-center cursor-pointer" @click="toggle($event, resource.value)">
                                                <fake-checkbox :checked="selected.includes(resource.value)" />
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 border-b border-grey-light text-80 border-60">
                                            {{ resource.display }}
                                        </td>
                                        <td v-for="pivot in resource.pivots" class="px-6 border-b border-grey-light text-right border-60">
                                            <input
                                            type="text"
                                            :placeholder="pivot.value"
                                            class="w-full form-control form-input form-input-bordered"
                                            ref="pivot_value"
                                            v-model="pivot.value"
                                            @keypress.enter.prevent="onEnterPivot($event)"
                                            @keyup="select($event, resource.value)"
                                            @focus="pivot.value=''">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <help-text class="error-text mt-2 text-danger" v-if="hasErrors">
                {{ firstError }}
            </help-text>

            <div class="help-text mt-3 w-full flex" :class="{ 'invisible': loading }">
                <span v-if="field.showCounts" class="pr-2 float-left border-60 whitespace-no-wrap" :class="{ 'border-r mr-2': field.helpText }">
                    {{ selected.length  }} / {{ available.length }}
                </span>

                <span class="float-left border-60" :class="{'border-r mr-2': field.showPreview }">
                    <help-text class="help-text" v-if="field.helpText"> {{ field.helpText }} </help-text>
                </span>

                <span v-if="field.showPreview" @click="togglePreview($event)" class="flex cursor-pointer select-none float-right">
                    <span class="pr-2">{{ __('Preview') }}</span>
                    <fake-checkbox class="flex" :checked="preview" />
                </span>
            </div>

        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            search: null,
            selected: [],
            selectingAll: false,
            available: [],
            preview: false,
            loading: true,
            showAlert: false,
            alertMessage: ''
        }
    },

    methods: {
        onEnter() {
            if(this.resources.length == 1){
                if(!this.selected.includes(this.resources[0].value)) {
                    this.selected.push(this.resources[0].value);
                }
                
                this.$refs.pivot_value[0].focus();
            }
        },

        onEnterPivot(event) {
            this.clearSearch();

            if(document.querySelector('[dusk="update-and-continue-editing-button"]') !== null){
                document.querySelector('[dusk="update-and-continue-editing-button"]').click();
            }
            else if(document.querySelector('[dusk="create-button"]') !== null){
                document.querySelector('[dusk="create-button"]').click();
            }
        },

        toggleMessage(message){
            this.alertMessage = message;
            this.showAlert = true;

            setInterval(() => this.showAlert = false, 3000);
        },

        setInitialValue() {
            let baseUrl = '/nova-vendor/nova-attach-pivot/';

            if(this.resourceId) {
                Nova.request(baseUrl + this.resourceName + '/' + this.resourceId + '/attachable/' + this.field.attribute + '?' + this.setQueryString()).then((data) => {
                    this.selected = data.data.selected || [];
                    this.available = data.data.available || [];
                    this.loading = false;
                });
            }
            else {
                Nova.request(baseUrl + this.resourceName + '/attachable/' + this.field.attribute + '?' + this.setQueryString()).then((data) => {
                    this.available = data.data.available || [];
                    this.loading = false;
                });
            }

        },

        fill(formData) {
            if(this.value){
                var elements = JSON.parse(this.value);
                var values = [];

                this.clearSearch();

                for (var i = elements.length - 1; i >= 0; i--) {
                    values.push({
                        value: elements[i],
                        pivots: this.resources.filter(resource => resource.value == elements[i])[0].pivots
                    });
                }
                formData.append(this.field.attribute, JSON.stringify(values));

            } else {
                formData.append(this.field.attribute, []);
            }
        },

        toggle(event, id){
            if(this.selected.includes(id)) {
                this.selected = this.selected.filter(selectedId => selectedId != id);
            } else {
                this.selected.push(id);
            }
        },

        select(event, id){
            if(!this.selected.includes(id)) {
                this.selected.push(id);
            }
        },

        selectAll() {
            var selected = this.selected;

            this.selectingAll = ! this.selectingAll;

            // search can return 0 results
            if(this.resources.length == 0) {
                return;
            }

            if(this.resources.length == 1 && this.selected == 1)
            {
                this.selected = [];
            }

            // add all resources
            if(! this.search && this.selectingAll) {
                selected = [];
                this.resources.forEach(resource => {
                    selected.push(resource.value)
                })
            }

            // remove all resources
            if(! this.search && ! this.selectingAll) {
                selected = [];
            }

            // append searched resources
            if(this.search && this.selectingAll) {
                this.resources.forEach(resource => {
                    selected.push(resource.value)
                })
            }

            // remove only searched items
            if(this.search && ! this.selectingAll) {

                let exludingIds = [];

                this.resources.forEach(resource => {
                    exludingIds.push(resource.value);
                })

                selected = selected.filter(id => exludingIds.includes(id) == false);
            }

            this.selected = selected;
        },

        clearSearch()
        {
            this.selectingAll = false;
            this.search = null;
        },

        checkIfSelectAllIsActive() {
            if(this.resources.length === 0 || this.preview) {
                this.selectingAll = false; return;
            }

            let visibleAndSelected = [];

            this.resources.forEach(resource => {
                if(this.selected.includes(resource.value)) {
                    visibleAndSelected.push(resource.value);
                }
            })

            this.selectingAll = visibleAndSelected.length == this.resources.length;
        },

        togglePreview(event){
            this.preview = ! this.preview;
        },

        setQueryString: function(){
            var params = {}
            if(this.field.pivotFields.length){
                params.pivots = this.field.pivotFields.join();
            }

            if(this.field.searchableFields.length){
                params.searchableFields = this.field.searchableFields.join();
            }

            return Object.keys(params)
                .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(params[k]))
                .join('&');
        }
    },

    computed: {
        resources: function() {
            if(this.preview) {
                return this.available.filter((resource) => {
                    return this.selected.includes(resource.value)
                });
            }

            if(this.search == null) {
                return this.available;
            }

            return this.available.filter((resource) => {
                return resource.searchableContent.toLowerCase().includes(this.search.toLowerCase())
            });
        },
        hasErrors: function() {
            return this.errors.errors.hasOwnProperty(this.field.attribute);
        },
        firstError: function() {
            return this.errors.errors[this.field.attribute][0]
        }
    },

    watch: {
        'search': {
            handler: function(search) {
                this.checkIfSelectAllIsActive();
            }
        },
        'selected': {
            handler: function (selected) {
                this.value = JSON.stringify(selected);
                this.checkIfSelectAllIsActive();
            },
            deep: true
        }
    }
};
</script>
