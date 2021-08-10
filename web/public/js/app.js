const STATUS_SUCCESS = 'success';
const STATUS_ERROR = 'error';
var app = new Vue({
	el: '#app',
	data: {
		login: '',
		pass: '',
		post: false,
		invalidLogin: false,
		invalidPass: false,
		invalidSum: false,
		posts: [],
		addSum: 0,
		amount: 0,
		likes: 0,
        likes_balance: -1,
        balance: 0.,
		commentText: '',
		boosterpacks: [],
        reply: [],
	},
	computed: {
		test: function () {
			var data = [];
			return data;
		}
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})

		axios
			.get('/main_page/get_boosterpacks')
			.then(function (response) {
				self.boosterpacks = response.data.boosterpacks;
			})
	},
	methods: {
		logout: function () {
			console.log ('logout');
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false

				form = new FormData();
				form.append("login", self.login);
				form.append("password", self.pass);

				axios.post('/main_page/login', form)
					.then(function (response) {
						if(response.data.user) {
							location.reload();
						}
						setTimeout(function () {
							$('#loginModal').modal('hide');
						}, 500);
					})
			}
		},
		addComment: function(id) {
			var self = this;
			if(self.commentText) {
                
				var comment = new FormData();
				comment.append('postId', id);
				comment.append('commentText', self.commentText);
                
                replyId = this.reply.id ? this.reply.id : null ;
                comment.append('replyId', replyId);
                 
				axios.post('/main_page/comment', comment).then(function (response) {
                    self.commentText = "";
                    this.reply = [];
                    axios.get('/main_page/get_post/' + id).then(function (response) {
                        self.post = response.data.post;
                        this.reply = [];
                    });
				});
			}

		},
		refill: function () {
			var self= this;
			if(self.addSum === 0){
				self.invalidSum = true
			}
			else{
				self.invalidSum = false
				sum = new FormData();
				sum.append('sum', self.addSum);
				axios.post('/main_page/add_money', sum)
					.then(function (response) {
						setTimeout(function () {
							$('#addModal').modal('hide');
						}, 500);
                        self.balance = response.data.user.balance;
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (type, id) {
            var self = this;
            const url = '/main_page/like_' + type + '/' + id;
            axios.get(url).then(function (response) {
                if(response.data.likes_balance>=0){
                    self.post.likes = response.data.likes;
                    self.likes_balance = response.data.likes_balance;
                }
            })
		},
		buyPack: function (id) {
			var self= this;
			var pack = new FormData();
			pack.append('id', id);
			axios.post('/main_page/buy_boosterpack', pack)
				.then(function (response) {
					self.amount = response.data.amount
					if(self.amount !== 0){
						setTimeout(function () {
							$('#amountModal').modal('show');
						}, 500);
					}
				})
		},
        addReply: function (comment) {
            this.reply = comment;
        },
        unsetReply: function (){
            this.reply = [];
        }
	}
});

Vue.component('comments', {
    props: ['model'],
  template: '<li>{{model.user.personaname}} / {{model.text}} / <a href="#" @click="addReply(model)">Reply</a> / <a role="button" @click="addLike(\'comment\', model.id)"><svg class="bi bi-heart" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 2.748l-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 01.176-.17C12.72-3.042 23.333 4.867 8 15z" clip-rule="evenodd"/></svg> {{ model.likes }}</a><ul v-if="isFolder"><comments :model="item" v-for="item in model.reply" v-bind:key="item.id"></comments></ul></li>',
  computed: {
    isFolder: function () {
        if(this.model.reply.length == 0) {
            return false;
        }else {
            return true;
        }
    }
  },
  methods: {
    addReply: function (comment) {
        app.reply = comment;
    }, 
    addLike: function (type, id) {
        var self = this;
        const url = '/main_page/like_' + type + '/' + id;
        axios.get(url).then(function (response) {
            if(response.data.likes_balance>=0){
                self.model.likes = response.data.likes;
                app.likes_balance = response.data.likes_balance;
            }
        })
    },
  }
});