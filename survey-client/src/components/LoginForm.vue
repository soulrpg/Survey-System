<script>
import {setCookie, getCookie} from "../utility/CookieManager"

async function postData(url, data = {}) {
    const response = await fetch(url, {
        method: 'POST',
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data, null, "\t")
    });
    return await response.json();
}

export default {
    data() {
        return {
            userData: {
                username: '',
                password: '',
            },
            msg: '',
            error: '',
            showLogin: true,
        }
    },
    mounted() {
        this.showLogin = getCookie('surveyUserToken') === null;
    },
    methods: {
        login() {
            postData('http://localhost:8000/api/login', this.userData)
            .then((data) => {
                if (data.token !== undefined && data.token.length > 0) {
                    this.userData.email = '';
                    this.password = '';
                    setCookie('surveyUserToken', data.token, 1 / 48)
                    this.showLogin = false;
                    this.msg = 'Login successfull.';
                } else {
                    this.error = data.message ?? '';
                }
            });
        },
    }
}
</script>

<template>
    <div v-if="showLogin">
        <h2>Login</h2>
        <p v-if="msg">{{msg}}</p>
        <p v-if="error">{{error}}</p>
        <form @submit.prevent="login" action="/" method="post">
            <label for="email">Email:</label><br/>
            <input type="text" id="email" v-model="userData.username"><br/>
            <label for="password">Password:</label><br/>
            <input type="password" id="password" v-model="userData.password"/><br/>
            <input type="submit" value="Login"/>
        </form>
    </div>
    <div v-else>
        <h2>You are already logged in!</h2>
    </div>
</template>

<style></style>