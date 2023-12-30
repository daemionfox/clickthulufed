# ActivityPub Implementation in ClickthuluFed

In order for ClickthuluFed to be part of the social media landscape, the following things need to be implemented:

## WebFinger
1) ~~Generate a webfinger response for the request~~
   1) ~~Comic~~
   2) ~~User~~

## Comic/User 
1) ~~Generate a standard actor content response~~

## Inbox

### Follow
1) Add the actor to the subscribed table
2) generate a response and send it to the actor's inbox

### Undo Follow
1) Remove the actor from the subscribed table
2) generate a response and send it to the actor's inbox

### Post Reply
### Boost
### Reply to Reply
### Unboost
### Delete Reply

## Followers
1) ~~Show collection for a comic~~
   1) ~~show subset of followers based on a per-page basis~~
2) ~~Users have no followers~~

## Following
1) Show all subscribed comics for a user
2) Comics have no following

## Outbox
1) Show all posts (comic) that have been made
2) Show all comments (user) that an actor has made