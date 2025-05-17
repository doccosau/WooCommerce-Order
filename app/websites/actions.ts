"use server"

import { createClient } from "@/lib/supabase/server"
import { revalidatePath } from "next/cache"
import { redirect } from "next/navigation"
import { z } from "zod"

// Schema validation cho form website
const websiteSchema = z.object({
  id: z.string().optional(),
  name: z.string().min(1, "Tên website không được để trống"),
  url: z.string().url("URL không hợp lệ").min(1, "URL không được để trống"),
  consumer_key: z.string().min(1, "Consumer key không được để trống"),
  consumer_secret: z.string().min(1, "Consumer secret không được để trống"),
  status: z.enum(["active", "inactive", "pending"]).default("active"),
})

export type WebsiteFormData = z.infer<typeof websiteSchema>

// Lấy danh sách website
export async function getWebsites() {
  const supabase = createClient()

  const { data, error } = await supabase.from("websites").select("*").order("created_at", { ascending: false })

  if (error) {
    console.error("Error fetching websites:", error)
    return []
  }

  return data
}

// Lấy thông tin một website
export async function getWebsite(id: string) {
  const supabase = createClient()

  const { data, error } = await supabase.from("websites").select("*").eq("id", id).single()

  if (error) {
    console.error("Error fetching website:", error)
    return null
  }

  return data
}

// Thêm website mới
export async function createWebsite(formData: FormData) {
  const supabase = createClient()

  // Chuyển đổi FormData thành object
  const rawData = {
    name: formData.get("name"),
    url: formData.get("url"),
    consumer_key: formData.get("consumer_key"),
    consumer_secret: formData.get("consumer_secret"),
    status: formData.get("status") || "active",
  }

  // Validate dữ liệu
  const validationResult = websiteSchema.safeParse(rawData)

  if (!validationResult.success) {
    return {
      success: false,
      errors: validationResult.error.flatten().fieldErrors,
    }
  }

  // Thêm website vào database
  const { data, error } = await supabase.from("websites").insert([validationResult.data]).select()

  if (error) {
    console.error("Error creating website:", error)
    return {
      success: false,
      errors: { server: [error.message] },
    }
  }

  // Revalidate path và redirect
  revalidatePath("/websites")
  redirect("/websites")
}

// Cập nhật website
export async function updateWebsite(formData: FormData) {
  const supabase = createClient()

  // Chuyển đổi FormData thành object
  const rawData = {
    id: formData.get("id"),
    name: formData.get("name"),
    url: formData.get("url"),
    consumer_key: formData.get("consumer_key"),
    consumer_secret: formData.get("consumer_secret"),
    status: formData.get("status") || "active",
  }

  // Validate dữ liệu
  const validationResult = websiteSchema.safeParse(rawData)

  if (!validationResult.success) {
    return {
      success: false,
      errors: validationResult.error.flatten().fieldErrors,
    }
  }

  const { id, ...websiteData } = validationResult.data

  // Cập nhật website trong database
  const { error } = await supabase.from("websites").update(websiteData).eq("id", id)

  if (error) {
    console.error("Error updating website:", error)
    return {
      success: false,
      errors: { server: [error.message] },
    }
  }

  // Revalidate path và redirect
  revalidatePath("/websites")
  redirect("/websites")
}

// Xóa website
export async function deleteWebsite(id: string) {
  const supabase = createClient()

  // Xóa website từ database
  const { error } = await supabase.from("websites").delete().eq("id", id)

  if (error) {
    console.error("Error deleting website:", error)
    return {
      success: false,
      error: error.message,
    }
  }

  // Revalidate path
  revalidatePath("/websites")
  return { success: true }
}

// Kiểm tra kết nối WooCommerce
export async function testWooCommerceConnection(formData: FormData) {
  const url = formData.get("url") as string
  const consumer_key = formData.get("consumer_key") as string
  const consumer_secret = formData.get("consumer_secret") as string

  try {
    // Tạo URL API WooCommerce
    const apiUrl = `${url}/wp-json/wc/v3/products?per_page=1`

    // Tạo Basic Auth header
    const authString = Buffer.from(`${consumer_key}:${consumer_secret}`).toString("base64")

    // Gọi API
    const response = await fetch(apiUrl, {
      headers: {
        Authorization: `Basic ${authString}`,
      },
      cache: "no-store",
    })

    if (!response.ok) {
      throw new Error(`API responded with status: ${response.status}`)
    }

    return { success: true }
  } catch (error) {
    console.error("Error testing WooCommerce connection:", error)
    return {
      success: false,
      error: error instanceof Error ? error.message : "Lỗi kết nối không xác định",
    }
  }
}
