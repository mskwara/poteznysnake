<template>
  <div>
    <md-table v-model="list" md-sort="score" md-sort-order="asc" md-card>
      <md-table-toolbar>
        <h1 class="md-title">TOP 10</h1>
      </md-table-toolbar>

      <md-table-row slot="md-table-row" slot-scope="{ item }">
        <md-table-cell md-label="Name" md-sort-by="name">{{ item.name }}</md-table-cell>
        <md-table-cell md-label="Score" md-sort-by="score">{{ item.score }}</md-table-cell>
        <md-table-cell md-label="Mode" md-sort-by="score" style="text-transform: uppercase">{{ item.mode }}</md-table-cell>
      </md-table-row>
    </md-table>
  </div>
</template>

<script>
  export default {
    props: ['list'],
    methods: {

    }
  };
</script>

<style>
.md-table-cell {
  text-align: left;
}
</style>
