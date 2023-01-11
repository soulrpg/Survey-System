<script>
import Option from './Option.vue';
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
    props: ['questionData'],
    components: {
        Option,
    },
    data() {
        return {
            optionsVisible: false,
            option: [],
            newOption: {
                name: '',
                question: this.questionData.id,
            },
            error: '',
        }
    },
    methods: {
        updateQuestion() {
            postData(`http://localhost:8000/api/question/update/${this.questionData.id}`, this.questionData, 'PUT')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateQuestionList();
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        deleteQuestion() {
            postData(`http://localhost:8000/api/question/delete/${this.questionData.id}`, {}, 'DELETE')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateQuestionList()
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        addNewOption() {
            postData('http://localhost:8000/api/option/create', this.newOption)
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    updateOptionList()
                } else {
                    this.error = data.message ?? '';
                }
            });
        },
        changeOptionsVisibility() {
            this.optionsVisible = !this.questionVisible;
            if (this.optionssVisible) {
                upadateOptionList()
            }
        },
        updateOptionList() {
            getData(`http://localhost:8000/api/question/list-options/${this.questionData.id}`)
            .then((data) => {
                this.$options = data
                console.log(this.options)
            });
        }
    }
}
</script>

<template>
    <h2>{{ questionData.title }}</h2>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="updateQuestion" action="/" method="post">
        <label for="title">Title:</label><br/>
        <input type="text" id="title" v-model="questionData.title"><br/>
        <label for="description">Description:</label><br/>
        <textarea rows="3" cols="50" id="description" v-model="questionData.description"/><br/>
        <input type="hidden" id="identifier" v-model="questionData.id"/>
        <input type="submit" value="Update Question"/>
    </form>
    <form @submit.prevent="deleteQuestion" action="/" method="post">
        <input type="hidden" id="identifier" v-model="questionData.id"/>
        <input type="submit" value="Delete Question"/>
    </form>
    <button @click="changeOptionsVisibility">Show options</button>
    <div v-show="optionsVisible">
        <Option
            v-for="option in options"
            :key="option.id"
            :optionData="{id: option.id, name: option.name}"
        />
        <h3>Add new option</h3>
        <p v-if="error">{{error}}</p>
        <form @submit.prevent="addNewOption" action="/" method="post">
            <label for="name">Name:</label><br/>
            <input type="text" id="name" v-model="newOption.name"><br/>
            <input type="hidden" id="identifier" v-model="newOption.question"/>
            <input type="submit" value="Create New Option"/>
        </form>
    </div>
</template>

<style></style>