<script>
import {postData} from "../utility/Utility"

export default {
    props: ['optionData', 'index'],
    data() {
        return {
            option: [],
            error: '',
        }
    },
    methods: {
        updateOption() {
            postData(`http://localhost:8000/api/option/update/${this.index}`, this.optionData, 'PUT')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateOptionList();
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        deleteOption() {
            postData(`http://localhost:8000/api/option/delete/${this.index}`, {}, 'DELETE')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateOptionList()
                } else {
                    this.error = data.msg ?? '';
                }
            });
        }
    }
}
</script>

<template>
    <h2>{{ optionData.name }}</h2>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="updateOption" action="/" method="post">
        <label for="name">Name:</label><br/>
        <input type="text" id="Name" v-model="optionData.name"><br/>
        <input type="hidden" id="index" v-bind="index"/>
        <input type="submit" value="Update Option"/>
    </form>
    <form @submit.prevent="deleteOption" action="/" method="post">
        <input type="hidden" id="index" v-bind="index"/>
        <input type="submit" value="Delete Option"/>
    </form>
</template>

<style></style>