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
    props: ['answerData'],
    emits: ['optionPicked'],
    data() {
        return {
            error: '',
            options: '',
        }
    },
    mounted() {
        this.getOptions();
    },
    methods: {
        getOptions() {
            getData(`http://localhost:8000/api/question/list-options/${this.answerData.id}`)
            .then((data) => {
                this.options = data
            });
        }
    }
}
</script>

<template>
    <h2>{{ answerData.title }}</h2>
    <h4>{{ answerData.description }}</h4>
    <div v-for="option in options">
        <label :for="option.id">{{ option.name }}</label>
        <input type="radio" :id="option.id" :name="answerData.id" :value="option.id" 
            @change="$emit('optionPicked', answerData.id, option.id)"
        />
    </div>
</template>

<style></style>