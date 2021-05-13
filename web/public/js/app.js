const STATUS_SUCCESS = 'success';
const STATUS_ERROR = 'error';
var app = new Vue({
	el: '#app',
	data: {
		email: '',
		pass: '',
		post: false,
		invalidEmail: false,
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

			self.invalidEmail = false;
			self.invalidPass = false;
			self.invalidLoginForm.hasError = false;

			if(self.email === ''){
				self.invalidEmail = true
			}
			else if(self.pass === ''){
				self.invalidPass = true
			}
			else{
				form = new FormData();
				form.append("email", self.email);
				form.append("password", self.pass);

                axios.get("csrf/get_token").then((response) => {
                    if (response.data.status !== "success") {
                        return;
                    }
                    form.append('csrf_token', response.data.token);
                    axios.post('/main_page/login', form)
                        .then(function (response) {
                            if (response.data.user) {
                                location.reload();
                                return;
                            }
                            if (response.data.status !== "success") {
								self.invalidLoginForm.message = response.data.error_message;
								self.invalidLoginForm.hasError = true;
							} else {
								setTimeout(function () {
									$('#loginModal').modal('hide');
								}, 500);
							}
                        })
                })
			}
		},
		addComment: function(id) {
			var self = this;
			if(self.commentText) {

				var comment = new FormData();
				comment.append('postId', id);
				comment.append('commentText', self.commentText);

				axios.post(
					'/main_page/comment',
					comment
				).then(function () {

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
			axios
				.get(url)
				.then(function (response) {
					self.likes = response.data.likes;
				})

		},
		buyPack: function (id) {
			var self= this;
			var pack = new FormData();
			pack.append('id', id);

			axios.get("csrf/get_token").then((response) => {
				if (response.data.status !== "success") {
					return;
				}
				pack.append('csrf_token', response.data.token);
				axios.post('/main_page/buy_boosterpack', pack)
					.then(function (response) {
						self.amount = response.data.amount
						if(self.amount !== 0){
							setTimeout(function () {
								$('#amountModal').modal('show');
							}, 500);
						}
					});
			});


		}
	}
});

