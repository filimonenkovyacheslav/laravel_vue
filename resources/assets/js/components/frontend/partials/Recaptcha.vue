<template>
  <div
  id="g-recaptcha"
  class="g-recaptcha"
  :data-sitekey="captcha.sitekey"
  :style="compact && compact==1 ? 'transform:scale(0.8);-webkit-transform:scale(0.8);transform-origin:0 0;-webkit-transform-origin:0 0;' : ''">
  </div>
</template>

<script>
export default {
  props: ['captcha', 'compact'],
  data () {
    return {
      widgetId: 0
    }
  },
  methods: {
    render () {
      if (window.grecaptcha) {
        this.widgetId = window.grecaptcha.render( $('.g-recaptcha')[0], { sitekey : typeof this.captcha.sitekey !== 'undefined' ? this.captcha.sitekey : '' });
      }
    }
  },
  mounted () {
    this.render()
  }
}
</script>
