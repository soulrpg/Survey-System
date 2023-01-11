<script>
import {setCookie, getCookie} from "../utility/CookieManager"

async function getData(url) {
    const response = await fetch(url, {
        method: 'GET',
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + getCookie('surveyUserToken')
        }
    });
    return await response.json();
}

async function postData(url, data = {}, method = 'POST') {
    const response = await fetch(url, {
        method: method,
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + getCookie('surveyUserToken')
        },
        body: JSON.stringify(data, null, "\t")
    });
    return await response.json();
}

export default {
    props: ['optionData'],
    data() {
        return {
            option: [],
            error: '',
        }
    },
    methods: {
        updateOption() {
            postData(`http://localhost:8000/api/option/update/${this.optionData.id}`, this.optionData, 'PUT')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateOptionList();
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        deleteOption() {
            postData(`http://localhost:8000/api/option/delete/${this.optionData.id}`, {}, 'DELETE')
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
        <input type="hidden" id="identifier" v-model="optionData.id"/>
        <input type="submit" value="Update Option"/>
    </form>
    <form @submit.prevent="deleteOption" action="/" method="post">
        <input type="hidden" id="identifier" v-model="optionData.id"/>
        <input type="submit" value="Delete Option"/>
    </form>
</template>

<style></style>