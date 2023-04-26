<template>
    <!--Sessionメッセージの表示部分-->
    <transition name="fade">
        <div class="c-message p-message" v-if="message">
            <i class="fa-solid fa-circle-check"></i>{{ message }}
        </div>
    </transition>
</template>

  <script>
    import { mapState } from 'vuex'

    export default {
      computed: {
        //state.message.contentを展開して取得
        ...mapState({
          message: state => state.message.content
        }),
      }
    }
  </script>
