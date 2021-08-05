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
		commentText: '',
		boosterpacks: [],
		invalidLoginForm : {
			message : '',
			hasError : false
		},
		invalidCommentForm : {
			message : '',
			hasError : false
		},
		invalidBalance : {
			message : '',
			hasError : false
		}
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
						if (response.data.status !== "success") {
							self.invalidLoginForm.message = response.data.error_message;
							self.invalidLoginForm.hasError = true;
						}
						if(response.data.user) {
							location.reload();
						}

						/*
						setTimeout(function () {
							$('#loginModal').modal('hide');
						}, 500);
						*/
						
					})
			}
		},
		addComment: function(id) {					
			var self = this;

			if(self.invalidCommentForm){
				self.invalidCommentForm.hasError = false;
			}

			if(self.commentText) {				
				var comment = new FormData();
				comment.append('postId', id);
				comment.append('commentText', self.commentText);
				axios.post(
					'/main_page/comment',
					comment
				).then(function (response) {
					
					if (response.data.status !== "success") {
						self.invalidCommentForm.message = response.data.error_message;
						self.invalidCommentForm.hasError = true;
					}

					if (response.data.status == "success") {
						self.post.coments = response.data.comments;
						//clean form 
						self.commentText = "";
					}

				});
			}
			else{
				self.invalidCommentForm.message = 'Pls write your comment text';
				self.invalidCommentForm.hasError = true;
			}

		},
		refill: function () {
			var self= this;
			if(self.invalidBalance){
				self.invalidBalance.hasError = false;
			}
			if(self.addSum === 0){
				self.invalidSum = true
			}
			else{
				self.invalidSum = false
				sum = new FormData();
				sum.append('sum', self.addSum);
				axios.post('/main_page/add_money', sum)
					.then(function (response) {
						if (response.data.status !== "success") {
							self.invalidBalance.message = response.data.error_message;
							self.invalidBalance.hasError = true;
						}
						
						if (response.data.status == "success") {
							//havent thinks now about that 
							setTimeout(function () {
								$('#addModal').modal('hide');
							}, 500);
						}
					})
			}
		},
		openPost: function (id) {			
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					if(self.invalidCommentForm){
						self.invalidCommentForm.hasError = false;
					}
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
			if(self.invalidCommentForm){
				self.invalidCommentForm.hasError = false;
			}
			const url = '/main_page/like_' + type + '/' + id;
			axios
				.get(url)
				.then(function (response) {
					if (response.data.status !== "success") {
						// or can ass new section in template || can allert message 
						self.invalidCommentForm.message = response.data.error_message;
						self.invalidCommentForm.hasError = true;
					}

					if (response.data.status == "success") {
						self.post = response.data.post;
						//self.likes = response.data.likes;
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
		}
	}
});

