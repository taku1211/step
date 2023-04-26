<template>
    <!--Pagitationボタン部分-->
    <div class="c-pagination">
        <RouterLink v-if="! isFirstPage" :to="`/${pageType}?page=${currentPage - 1}`" class="c-pagination__button c-pagination__button--first">
            prev
        </RouterLink>
        <RouterLink class="c-pagination__button" :to="`/${pageType}?page=1`" v-if="currentPage > 2">
            1
        </RouterLink>
        <RouterLink class="c-pagination__button c-pagination__button--noborder" :to="`/${pageType}?page=1`" v-if="currentPage > 3">
            ...
        </RouterLink>
        <RouterLink class="c-pagination__button" :to="`/${pageType}?page=`+(currentPage-1)" v-if="currentPage > 1">
            {{ currentPage-1 }}
        </RouterLink>
        <RouterLink class="c-pagination__button c-pagination__button--selected" :to="`/${pageType}?page=`+(currentPage)">
            {{ currentPage }}
        </RouterLink>
        <RouterLink class="c-pagination__button" :to="`/${pageType}?page=`+(currentPage+1)" v-if="currentPage < lastPage">
            {{ currentPage+1 }}
        </RouterLink>
        <RouterLink class="c-pagination__button c-pagination__button--noborder" to="/index?page=1" v-if="currentPage+2 < lastPage">
            ...
        </RouterLink>
        <RouterLink class="c-pagination__button" :to="`/${pageType}?page=`+(lastPage)" v-if="currentPage+1 < lastPage">
            {{ lastPage }}
        </RouterLink>
        <RouterLink v-if="! isLastPage" :to="`/${pageType}?page=${currentPage + 1}`" class="c-pagination__button c-pagination__button--last">
            next
        </RouterLink>
    </div>
</template>

<script>
export default {
  props: {
    currentPage: {
      type: Number,
      required: true
    },
    lastPage: {
      type: Number,
      required: true,
    },
    pageType: {
        type: String,
        required:true,
    }
  },
  computed: {
    //現在のページが最初のページかどうかの真偽確認
    isFirstPage () {
      return this.currentPage === 1
    },
    //現在のページが最終ページかどうかの真偽確認
    isLastPage () {
      return this.currentPage === this.lastPage
    }
  },
}
</script>
