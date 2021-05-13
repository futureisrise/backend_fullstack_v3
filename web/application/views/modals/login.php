<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Log in</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Please enter login</label>
                        <input type="email" class="form-control" id="inputEmail" aria-describedby="emailHelp" v-model="email" required>
                        <div class="invalid-feedback" v-if="invalidLogin">
                            Please write a username.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Please enter password</label>
                        <input type="password" class="form-control" id="inputPassword" v-model="pass" required>
                        <div class="invalid-feedback" v-show="invalidPass">
                            Please write a password.
                        </div>
                    </div>
                </form>
                <div class="alert alert-danger" role="alert" v-if="invalidLoginForm.hasError">
                    {{invalidLoginForm.message}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-primary" @click.prevent="logIn">Login</button>
            </div>
        </div>
    </div>
</div>