# Endpoints

## GET recent presentations

/ginger/api/v1/recentPresentations

```javascript
{
    "presentations": [
      {
        "id": 1,
        "name": "presentation name",
      },
      ...
    ]
}
```

## GET presentation

/ginger/api/v1/presentations/{id}

```javascript
{
    "id": 1,
    "name": "presentation name",
    "slides": [
        {
            "id": 1,
            "title": "Начало",
            "startTime": 0,
        },
        ...
    ]
}
```

## POST video

/gingerberry/api/v1/presentations/{id}

```javascript
// empty return
```
