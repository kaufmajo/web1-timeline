# Git

## Compare local branch with remote

git diff <mainbranch_path> <remotebranch_path>

```ps
$ git diff main origin/main
```

## Checkout remote branch 

As before, start by fetching the latest remote changes:

```ps
$ git fetch origin
```

This will fetch all of the remote branches for you. You can see the branches available for checkout with:

```ps
$ git branch -v -a
```

With the remote branches in hand, you now need to check out the branch you are interested in with -c to create a new local branch:

```ps
$ git switch -c test origin/test
```