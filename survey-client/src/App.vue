<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header>
    <nav>
      <RouterLink class="btn btn-link" to="/">Home</RouterLink>
      <RouterLink class="btn btn-link" to="/login">Login</RouterLink>
      <RouterLink class="btn btn-link" to="/survey/1">Take a survey</RouterLink>
      <RouterLink class="btn btn-link" to="/edit">Edit surveys</RouterLink>
    </nav>
  </header>

  <RouterView />
</template>

<style scoped>

</style>
