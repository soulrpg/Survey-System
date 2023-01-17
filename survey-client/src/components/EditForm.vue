<script>
import Survey from './Survey.vue';
import {postData, getData} from "../utility/Utility"

export default {
    components: {
        Survey,
    },
    data() {
        return {
            surveys: [],
            newSurvey: {
                title: '',
                public: false,
                descrption: '',
            },
            error: '',
        }
    },
    mounted() {
        this.updateSurveyList()
    },
    methods: {
        addNewSurvey() {
            postData('http://localhost:8000/api/survey/create', this.newSurvey)
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    updateSurveyList()
                } else {
                    this.error = data.message ?? '';
                }
            });
        },
        updateSurveyList() {
            getData('http://localhost:8000/api/survey')
            .then((data) => {
                this.surveys = data
                console.log(this.surveys)
            });
        }
    }
}
</script>

<template>
    <Survey
        v-for="survey in surveys"
        :key="survey.id"
        :surveyData="{id: survey.id, title: survey.title, public: survey.public, description: survey.description}"
    />
    <h2>Add new survey</h2>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="addNewSurvey" action="/" method="post">
        <label for="title">Title:</label><br/>
        <input type="text" id="title" v-model="newSurvey.title"><br/>
        <label for="public">Is Public:</label><br/>
        <input type="checkbox" id="public" v-model="newSurvey.public"/><br/>
        <label for="description">Description:</label><br/>
        <textarea rows="3" cols="50" id="description" v-model="newSurvey.description"/><br/>
        <input type="submit" value="Create New Survey"/>
    </form>
</template>

<style></style>