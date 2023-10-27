
# Reporting Channel RESTful API

A reporting channel API project made for studying purposes, following RESTful principles.

## API Reference

#### Get all items

```
  GET /api
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `deleted` | `bool`   |  Removed reports           |
| `type`    | `string` |  Report category           |
| `order`   | `string` |  Order results by column   |
|`order_dir`| `string` |  ASC or DESC               |
| `limit`   | `int`    |Max nº of results / starting index|
|`offset`| `int` |Max nº of results when `limit` is passed|


#### Create item

```
  POST /api
```

|Parameter|Type|Description           |
| :------ | :------- | :---------------------------- |
| `deleted` | `bool` | **Required** Removed reports  |
| `type`    |`string`| **Required** Report category  |
| `message` |`string`| **Required** Report message   |
|`is_identified`|`bool`| **Required** Anonymous report|


#### Get item

```
  GET /api/${id}
```

|Parameter|Type|Description           |
|:--------|:---| :--------------------- |
| `id` | `int` | **Required**. ID of item to fetch |


#### Update item

```
  PUT|PATCH /api/${id}
```

|Parameter|Type|Description           |
| :------ | :------- | :---------------------------- |
| `id` | `int` | **Required**. ID of item to fetch |
| `deleted` | `bool` | Removed reports  |
| `type`    |`string`| Report category  |
| `message` |`string`| Report message   |
|`is_identified`|`bool`| Anonymous report|

**Note:** At least one of the previous parameters must be passed

#### Delete item

```
  DELETE /api/${id}
```

|Parameter|Type|Description           |
|:--------|:---| :--------------------- |
| `id` | `int` | **Required**. ID of item to fetch |

## Creating development environment

1- Download the code in a zip file from repository

2- Download and install [Docker](https://www.docker.com/)

3- Unzip and store all files in a directory of your choice

4- Open terminal in the directory where all files are stored

5- Build the docker image with the command bellow:

```bash
  docker-compose build
```

6- Start a docker container on port 8000 with the command bellow:

```bash
  docker-compose up
```
