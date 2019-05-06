Vue.component('person', {
    template: '<div class="col-md-3 col-xs-6">' +
    '                        <div class="person--avatar"><img src="{{ image }}" /></div>' +
    '                        <div class="person--info">' +
    '                            <p class="person--full-name">{{ message }}</p>' +
    '                            <p class="person--position">{{ level }}</p>' +
    '                        </div>' +
    '                    </div>',
    // data is technically a function, so Vue won't
    // complain, but we return the same object
    // reference for each component instance
    props: ['image', 'username', 'level']
});


new Vue ({
    el: '.skills',
    methods: {
        'showSkills': function (categoryID) {
            // documents.get
            var skills = document.getElementsByClassName("skill");
            categoryID = parseInt(categoryID);

            var allCategories = document.getElementsByClassName("skill-category");

            _.map(allCategories, function(category) {
                category.className = category.className.replace(' active', '');
            });

            var currentCategory = document.getElementById("category" + categoryID);
            currentCategory.className = currentCategory.className + " active";

            _.map(skills, function(skill) {
                var skillCategoryId = parseInt(skill.getAttribute("data-category-id"), 10);
                if (skillCategoryId === categoryID) {
                    skill.className = skill.className.replace(' hidden', '');
                } else {
                    if (!skill.className.match(/hidden/)) {
                        skill.className = skill.className + ' hidden';
                    }
                }
            });

        },
        'searchUsers': function(skillID) {
            var success = function (results) {

                var allSkills = document.getElementsByClassName("skill");
                _.map(allSkills, function(skill) {
                    skill.className = skill.className.replace(' active', '');
                });

                var currentSkill = document.getElementById("skill" + skillID);
                currentSkill.className = currentSkill.className + " active";

                document.getElementById("users_skills_list").innerHTML = "";

                var data = results.body[0];

                if (data) {
                    console.log(data);

                    var personContainer = document.createElement("div");
                    personContainer.className = "col-md-3 col-xs-6";
                    var personAvatar = document.createElement("img");
                    personAvatar.className = "person--avatar";
                    personAvatar.setAttribute("src", data.image);
                    var personInfo = document.createElement("div");
                    personInfo.className = "person--info";
                    var personFullName = document.createElement("div");
                    personFullName.className = "person--full-name";
                    personFullName.innerHTML = data.name;
                    var personPosition = document.createElement("div");
                    personPosition.className = "person--position";
                    personPosition.innerHTML = data.level;

                    personInfo.appendChild(personFullName);
                    personInfo.appendChild(personPosition);
                    personContainer.appendChild(personAvatar);
                    personContainer.appendChild(personInfo);



                    document.getElementById("users_skills_list").appendChild(personContainer);
                }

            };
            var error = function (error) {
                console.log("There has been an error.");
            };


            this.$http.get('/lfh/' + skillID).then(success, error);
            console.log("Bla");
        }
    }
});

