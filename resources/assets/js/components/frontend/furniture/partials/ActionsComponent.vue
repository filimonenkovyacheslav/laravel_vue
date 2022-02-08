<template>
    <ul class="actions">
        <li class="share-btn btn-group dropleft" v-if="inArray('share-btn', items)">
            <span class="dropdown-toggle" title="Social Share" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-share-alt"></i>
            </span>
            <div class="dropdown-menu share_tooltip">
                <a v-bind:href="'http://www.facebook.com/sharer.php?u=' + window.location.href" target="_blank"><i class="fa fa-facebook"></i></a>
                <a v-bind:href="'https://twitter.com/share?url=' + window.location.href + '&text=' + entityData.title" target="_blank"><i class="fa fa-twitter"></i></a>
                <a v-bind:href="'http://pinterest.com/pin/create/link/?url=' + window.location.href + '&media=' + '' + '&description=' + entityData.title" target="_blank"><i class="fa fa-pinterest"></i></a>
                <a v-bind:href="'https://www.linkedin.com/shareArticle?mini=true&title=' + entityData.title + '&url=' + window.location.href" target="_blank"><i class="fa fa-linkedin"></i></a>
                <a v-bind:href="'https://plus.google.com/share?url=' + window.location.href" target="_blank"><i class="fa fa-google-plus"></i></a>
                <a v-if="entityData.user" v-bind:href="'mailto:' + entityData.user.email" target="_blank"><i class="fa fa-envelope"></i></a>
            </div>
        </li>
        <li class="favorite-btn" v-if="inArray('favorite-btn', items)" v-on:click="userRole ? toggleFavoriteArt(entityData.id) : window.location.href = route('login')">
            <span class="add_fav" title="Add to Favorite"><i v-bind:class="'fa ' + (entityData.is_favorite ? 'fa-heart' : 'fa-heart-o')"></i></span>
        </li>
        <li class="edit-btn" v-if="userRole=='administrator'">
            <span class="edit_entity" title="Edit">
                <a v-bind:href="route('furniture.edit.admin', {'id': entityData.id})" target="_blank">
                    <span><i class="fa fa-edit"></i></span>
                </a>
            </span>
        </li>
        <li class="edit-btn" v-if="userRole=='administrator'">
            <span class="delete_entity" title="Delete">
                <a v-bind:href="route('furniture.delete.admin', {'id': entityData.id})">
                    <span><i class="fa fa-times"></i></span>
                </a>
            </span>
        </li>
    </ul>
</template>

<script>
    /**
     * Get data via props in blade template <vue_template :props></vue_template>
     */
    export default {
        props: ['entityData', 'userRole', 'items']
    }
</script>
