<script>
import Question from './Question.vue';
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
    props: ['surveyData'],
    components: {
        Question,
    },
    data() {
        return {
            questionsVisible: false,
            questions: [],
            newQuestion: {
                title: '',
                survey: this.surveyData.id,
                descrption: '',
            },
            error: '',
        }
    },
    methods: {
        updateSurvey() {
            postData(`http://localhost:8000/api/survey/update/${this.surveyData.id}`, this.surveyData, 'PUT')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateSurveyList();
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        deleteSurvey() {
            postData(`http://localhost:8000/api/survey/delete/${this.surveyData.id}`, {}, 'DELETE')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateSurveyList()
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        addNewQuestion() {
            postData('http://localhost:8000/api/question/create', this.newQuestion)
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.updateQuestionList()
                } else {
                    this.error = data.message ?? '';
                }
            });
        },
        changeQuestionsVisibility() {
            this.questionsVisible = !this.questionsVisible;
            if (this.questionsVisible) {
                this.updateQuestionList()
            }
        },
        updateQuestionList() {
            console.log('updateQuestionList');
            getData(`http://localhost:8000/api/survey/list-questions/${this.surveyData.id}`)
            .then((data) => {
                this.questions = data
                console.log(this.surveys)
            });
        }
    }
}
</script>

<template>
    <h2>{{ surveyData.title }}</h2>
    <h3>Id: {{ surveyData.id }}</h3>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="updateSurvey" action="/" method="post">
        <label for="title">Title:</label><br/>
        <input type="text" id="title" v-model="surveyData.title"><br/>
        <label for="public">Is Public:</label><br/>
        <input type="checkbox" id="public" v-model="surveyData.public"/><br/>
        <label for="description">Description:</label><br/>
        <textarea rows="3" cols="50" id="description" v-model="surveyData.description"/><br/>
        <input type="hidden" id="identifier" v-model="surveyData.id"/>
        <input type="submit" value="Update Survey"/>
    </form>
    <form @submit.prevent="deleteSurvey" action="/" method="post">
        <input type="hidden" id="identifier" v-model="surveyData.id"/>
        <input type="submit" value="Delete Survey"/>
    </form>
    <button @click="changeQuestionsVisibility">Show questions</button>
    <div v-show="questionsVisible">
        <Question
            v-for="question in questions"
            :key="question.id"
            :index="question.id"
            :questionData="{survey: surveyData.id, title: question.title, description: question.description}"
        />
        <h3>Add new question</h3>
        <form @submit.prevent="addNewQuestion" action="/" method="post">
            <label for="title">Title:</label><br/>
            <input type="text" id="title" v-model="newQuestion.title"><br/>
            <label for="description">Description:</label><br/>
            <textarea rows="3" cols="50" id="description" v-model="newQuestion.description"/><br/>
            <input type="hidden" id="identifier" v-model="newQuestion.survey"/>
            <input type="submit" value="Create New Question"/>
        </form>
    </div>
</template>

<style></style>