const STATUS_SUCCESS = 'success';
const STATUS_ERROR = 'error';


Vue.component("tree-item", {
	template: "#item-template",
	props: {
		item: Object
	},
	data: function() {
		return {
			isOpen: false
		};
	},
	computed: {
		isFolder: function() {
			return this.item.reply && this.item.reply.length;
		}
	},
	methods: {
		toggle: function() {
			if (this.isFolder) {
				this.isOpen = !this.isOpen;
			}
		},
		makeFolder: function() {
			if (!this.isFolder) {
				this.$emit("make-folder", this.item);
				this.isOpen = true;
			}
		}
	}
});

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
		history: [],
		user_balance: [],
		addSum: 0,
		amount: 0,
		likes: 0,
		commentText: '',
		boosterpacks: [],
		treeData: ''
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
		makeFolder: function(item) {
			Vue.set(item, "reply", []);
			this.addItem(item);
		},
		addItem: function(item) {
			item.children.push({
				name: "new stuff"
			});
		},
		addReply: function(comentId) {
			document.querySelector("#addReplyId").value = comentId;
		},
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
				comment.append('replyId', document.querySelector("#addReplyId").value);

				axios.post(
					'/main_page/comment',
					comment
				).then(function (response) {
					document.querySelector("#addReplyId").value = '';
					self.reloadPost(id);
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
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					self.treeData = {'id':0, 'user':{ "id": 0, "personaname": "Coments"}, 'reply': response.data.post.coments};
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		getHistory: function (id) {
			var self= this;
			axios
				.get('/main_page/get_history/')
				.then(function (response) {
					self.history = response.data.history;
					self.user_balance = response.data.user_balance;
					console.log(response);
					if(self.history){
						setTimeout(function () {
							$('#historyModal').modal('show');
						}, 500);
					}
				})
		},
		reloadPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					self.treeData = {'id':0, 'user':{ "id": 0, "personaname": "Coments"}, 'reply': response.data.post.coments};
				})
		},
		addLike: function (type, id) {
			var self = this;
			const url = '/main_page/like_' + type + '/' + id;

			axios
				.get(url)
				.then(function (response) {
					if(response.data.msg){
						self.reloadPost(self.post.id);
					}else{
						alert('You have no likes left');
					}
				})

		},

		buyPack: function (id) {
			var self= this;
			var pack = new FormData();
			pack.append('id', id);
			axios.post('/main_page/buy_boosterpack', pack)
				.then(function (response) {
					if(response.data.amount){
						self.amount = response.data.amount
						if(self.amount !== 0){
							setTimeout(function () {
								$('#amountModal').modal('show');
							}, 500);
						}
					}else {
						alert('There is no balance in the account or another error');
					}

				})
		}
	}
});

