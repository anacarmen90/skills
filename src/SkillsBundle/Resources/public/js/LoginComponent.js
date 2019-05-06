
if (document.getElementById("login_modal_link")) {
    // register modal component
    Vue.component('modal', {
        template: '#modal-template'
    });

// start app
    new Vue({
        el: '#login_modal_link',
        data: {
            showModal: false
        }
    });
}
