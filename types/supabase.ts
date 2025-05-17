export interface Website {
  id: string
  name: string
  url: string
  consumer_key: string
  consumer_secret: string
  status: string
  last_sync: string | null
  created_at: string
  updated_at: string
}

export interface Product {
  id: string
  website_id: string
  woo_id: number
  name: string
  sku: string | null
  price: number | null
  regular_price: number | null
  sale_price: number | null
  stock_quantity: number | null
  stock_status: string | null
  categories: any
  images: any
  attributes: any
  description: string | null
  short_description: string | null
  status: string | null
  created_at: string
  updated_at: string
}

export interface Order {
  id: string
  website_id: string
  woo_id: number
  order_number: string | null
  status: string | null
  currency: string | null
  total: number | null
  customer_id: string | null
  customer_name: string | null
  customer_email: string | null
  billing_address: any
  shipping_address: any
  payment_method: string | null
  payment_method_title: string | null
  items: any
  created_at: string
  updated_at: string
  order_date: string | null
}

export interface Customer {
  id: string
  website_id: string
  woo_id: number
  email: string | null
  first_name: string | null
  last_name: string | null
  username: string | null
  billing_address: any
  shipping_address: any
  phone: string | null
  total_spent: number | null
  orders_count: number | null
  created_at: string
  updated_at: string
}

export interface SyncLog {
  id: string
  website_id: string
  entity_type: string
  status: string
  items_count: number | null
  error_message: string | null
  started_at: string
  completed_at: string | null
}

export interface Role {
  id: string
  name: string
  description: string | null
  created_at: string
}

export interface Permission {
  id: string
  name: string
  description: string | null
  created_at: string
}

export interface RolePermission {
  role_id: string
  permission_id: string
  created_at: string
}

export interface UserProfile {
  id: string
  full_name: string | null
  avatar_url: string | null
  role_id: string | null
  created_at: string
  updated_at: string
}

export interface Setting {
  id: string
  key: string
  value: any
  created_at: string
  updated_at: string
}
