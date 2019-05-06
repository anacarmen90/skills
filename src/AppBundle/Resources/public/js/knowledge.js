new Vue({
  el: '.skills-list-box',
  methods: {
    'showSkills': function (categoryID) {
      _.map(document.getElementsByClassName('skill--profile'), function (element) {
        if (!categoryID) {
          element.style.display = 'block';
        }
        else {
          if (element.getAttribute('data-tags').split('|').indexOf(categoryID.toString()) !== -1) {
            element.style.display = 'block';
          }
          else {
            element.style.display = 'none';
          }
        }
      });
      _.map(document.getElementsByClassName('skill-category'), function (element) {
        element.classList.remove('active');
      });
      document.getElementById(categoryID ? ('category-li-' + categoryID) : 'reset-categories').classList.add('active');
    },
    'saveLevel': function (skillId, level) {

      var success = function (data) {
        console.log('a mers');
      };
      var error = function () {
        alert('didn\'t work. sorry.')
      };
      this.$http.post('/lfh/update', {
        skill: skillId,
        level: level
      }).then(success, error);
    }
  }
});
new Vue({
  el: '.help-button',
  methods: {
    'toggleAvail': function () {
      var success = function (data) {
        var newClass = data.body.avail ? 'true' : 'false'
        document.getElementById('can-help').className = newClass;
      };
      var error = function () {
        alert('didn\'t work. sorry.')
      };
      this.$http.get('/toggle_avail').then(success, error);
    }
  }
});